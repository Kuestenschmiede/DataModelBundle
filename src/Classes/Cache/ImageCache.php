<?php

namespace gutesio\DataModelBundle\Classes\Cache;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;

class ImageCache
{
    private $localCachePath;
    private $localPublicPath;
    private $cacheCount = 0;
    private $maxDownloadsPerMinute;
    private $negativeCacheTtl;
    private $enableStaleWhileRevalidate;
    // Strict probing defaults
    private int $strictConnectTimeoutMs = 300; // 0.3s
    private int $strictReadTimeoutMs = 800;    // 0.8s
    private int $strictNegativeTtl = 120;      // seconds

    public function __construct(string $localCachePath, string $localPublicPath)
    {
        $this->localCachePath = rtrim($localCachePath, '/');
        $this->localPublicPath = rtrim($localPublicPath, '/');
        $this->maxDownloadsPerMinute = (int) (getenv('IMAGECACHE_MAX_DOWNLOADS_PER_MINUTE') ?: 60);
        $this->negativeCacheTtl = (int) (getenv('IMAGECACHE_NEGATIVE_CACHE_TTL') ?: 600); // 10 minutes
        $this->enableStaleWhileRevalidate = (bool) (getenv('IMAGECACHE_ENABLE_SWR') !== '0');

        // Allow tuning strict mode via env
        $this->strictConnectTimeoutMs = (int) (getenv('IMAGECACHE_STRICT_CONNECT_MS') ?: $this->strictConnectTimeoutMs);
        $this->strictReadTimeoutMs = (int) (getenv('IMAGECACHE_STRICT_READ_MS') ?: $this->strictReadTimeoutMs);
        $this->strictNegativeTtl = (int) (getenv('IMAGECACHE_STRICT_NEG_TTL') ?: $this->strictNegativeTtl);

        if (!is_dir($this->localCachePath)) {
            mkdir($this->localCachePath, 0777, true);
        }
    }

    public static function purgeCache(string $localCachePath) {
        if (!is_dir($localCachePath)) {
            return false;
        }

        $items = array_diff(scandir($localCachePath), ['.', '..']);

        foreach ($items as $item) {
            $itemPath = rtrim($localCachePath, '/') . $item;

            if (is_file($itemPath)) {
                $fileModifiedTime = filemtime($itemPath);
                $now = time();
                if (($now - $fileModifiedTime) > 86400) {
                    if (!unlink($itemPath)) {
                        return false;
                    }
                }
            } elseif (is_dir($itemPath)) {
                if (!purgeCache($itemPath)) {
                    return false;
                }
            }
        }

        return true;
    }


    private function removeGetParams($url) {
        $urlParts = parse_url($url);

        if ($urlParts === false || !isset($urlParts['path'])) {
            return false;
        }

        return $urlParts['path'];
    }


    private function appendToImageName($url, $extendedParam) {
        $info = pathinfo($url);
        $newName = $info['filename'] . $extendedParam . "." . $info['extension'];
        $newPathName = $info['dirname'] . '/' . $newName;
        return $newPathName;
    }

    /**
     * Strict resolver: returns only URLs that are locally present or verifiably available on the CDN.
     * Never triggers a download. Returns an array with keys: status (local|remote|missing) and url.
     */
    public function resolveImageStrict(string $imagePath, string $extendedParam = ''): array
    {
        $paths = $this->computePaths($imagePath, $extendedParam);
        if ($paths === null) {
            return ['status' => 'missing', 'url' => ''];
        }

        [$cdnUrl, $localPath, $destinationPath] = $paths;

        // Serve local if present
        if (file_exists($destinationPath) && filesize($destinationPath) > 0) {
            return ['status' => 'local', 'url' => $this->localPublicPath . $localPath];
        }

        // Respect negative cache with a shorter TTL in strict mode
        if ($this->isNegativelyCachedStrict($destinationPath)) {
            return ['status' => 'missing', 'url' => ''];
        }

        // Probe CDN quickly
        $available = $this->probeRemoteAvailable($cdnUrl);
        if ($available) {
            return ['status' => 'remote', 'url' => $cdnUrl];
        }

        $this->writeNegativeCache($destinationPath);
        return ['status' => 'missing', 'url' => ''];
    }

    public function getImage(string $imagePath, string $extendedParam = '', int $time=172800, int $cacheCount=4, $ignoreExpiry = false): string
    {
        $localPath = $this->removeGetParams($imagePath);
        if (!$localPath) {
            return false;
        }

        $cdnUrl = $imagePath;
        $sourcePath = ltrim($localPath, '/');
        $downloadPath = rtrim($this->localCachePath, '/') . '/' . $sourcePath;

        if ($extendedParam) {
            $localPath = $this->appendToImageName($localPath, $extendedParam);
            $sourcePath = ltrim($localPath, '/');
            $destinationPath = rtrim($this->localCachePath, '/') . '/' . $sourcePath;
        } else {
            $destinationPath = $downloadPath;
        }

        $fileExists = file_exists($destinationPath) && filesize($destinationPath) > 0;
        $expired = $this->isCacheExpired($destinationPath, $time, $ignoreExpiry);

        if (!$expired) {
            return $this->localPublicPath . $localPath;
        }

        $lockHandle = null;
        $lockPath = $this->getLockPath($destinationPath);

        if ($this->isNegativelyCached($destinationPath)) {
            return $fileExists ? ($this->localPublicPath . $localPath) : $cdnUrl;
        }

        if ($this->enableStaleWhileRevalidate && $fileExists) {
            if ($this->tryAcquireLock($lockHandle, $lockPath) && $this->canDownloadNow($cacheCount)) {
                $this->refreshInBackground($cdnUrl, $destinationPath, $lockHandle, $lockPath);
            }
            return $this->localPublicPath . $localPath;
        }

        if ($this->cacheCount < $cacheCount && $this->canDownloadNow($cacheCount)) {
            $downloaded = false;
            if ($this->tryAcquireLock($lockHandle, $lockPath)) {
                try {
                    $downloaded = $this->downloadImage($cdnUrl, $destinationPath);
                } finally {
                    $this->releaseLock($lockHandle, $lockPath);
                }
            }

            if ($downloaded) {
                $this->cacheCount++;
                return $this->localPublicPath . $localPath;
            }
        }

        return $fileExists ? ($this->localPublicPath . $localPath) : $cdnUrl;
    }

    //default 48h, max. 5000 images
    public function getImages(array $imagePaths, int $time=172800, int $cacheCount=5000): bool
    {
        $sourcePaths = [];
        $destinationPaths = [];
        foreach ($imagePaths as $imagePath) {
            $localPath = is_array($imagePath) && key_exists('image', $imagePath) ? $this->removeGetParams($imagePath['image']) : false;
            if (!$localPath) {
                continue;
            }
            $cdnUrl = $imagePath['image'];
            $extendedParam = $imagePath['extendedParam'];

            $sourcePath = ltrim($localPath, '/');
            $downloadPath = rtrim($this->localCachePath, '/') . '/' . $sourcePath;

            if ($extendedParam) {
                $localPath = $this->appendToImageName($localPath, $extendedParam);
                $sourcePath = ltrim($localPath, '/');
                $destinationPath = rtrim($this->localCachePath, '/') . '/' . $sourcePath;
            } else {
                $destinationPath = $downloadPath;
            }

            if ($this->isCacheExpired($destinationPath, $time)) {
                $fileExists = file_exists($destinationPath) && filesize($destinationPath) > 0;
                if ($this->cacheCount < $cacheCount && $this->canDownloadNow($cacheCount) && !$this->isNegativelyCached($destinationPath)) {
                    $lockHandle = null;
                    $lockPath = $this->getLockPath($destinationPath);
                    if ($this->tryAcquireLock($lockHandle, $lockPath)) {
                        try {
                            if ($this->downloadImage($cdnUrl, $destinationPath)) {
                                $this->cacheCount++;
                            }
                        } finally {
                            $this->releaseLock($lockHandle, $lockPath);
                        }
                    }
                }
            }
        }

        return true;
    }

    //default 48h
    private function isCacheExpired(string $filePath, int $time=172800, bool $ignoreExpiry = false): bool
    {
        if (!file_exists($filePath) || filesize($filePath) <= 0) {
            return true;
        }

        if ($ignoreExpiry === true) {
            return false;
        }

        $fileTimestamp = filemtime($filePath);

        return (time() - $fileTimestamp) > $time;
    }

    private function downloadImage(string $url, string $localFilePath): bool
    {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,application/pdf,*/*;q=0.8',
        ];

        if (file_exists($localFilePath) && filesize($localFilePath) > 0) {
            $headers['If-Modified-Since'] = gmdate('D, d M Y H:i:s T', filemtime($localFilePath));
        }

        $etagPath = $this->getEtagPath($localFilePath);
        if (file_exists($etagPath)) {
            $etag = trim(@file_get_contents($etagPath));
            if ($etag !== '') {
                $headers['If-None-Match'] = $etag;
            }
        }

        $client = new Client([
            'timeout' => 20,
            'connect_timeout' => 5,
            'http_errors' => false,
            'verify' => true,
            'allow_redirects' => [
                'max'             => 5,
                'strict'          => false,
                'referer'         => true,
                'protocols'       => ['http', 'https'],
                'track_redirects' => true
            ],
            'headers' => $headers
        ]);

        $destinationDir = dirname($localFilePath);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0777, true);
        }

        $tempFilePath = $localFilePath . '.tmp';

        try {
            $fileHandle = fopen($tempFilePath, 'w');
            $response = $client->get($url, ['sink' => $fileHandle]);

            if (is_resource($fileHandle)) {
                fclose($fileHandle);
            }

            if ($response->getStatusCode() === 304) {
                if (file_exists($tempFilePath)) {
                    unlink($tempFilePath);
                }

                if (file_exists($localFilePath)) {
                    touch($localFilePath);
                    return true;
                }

                return false;
            }

            if ($response->getStatusCode() === 200) {
                clearstatcache(true, $tempFilePath);
                if (filesize($tempFilePath) === 0) {
                    if (file_exists($tempFilePath)) {
                        unlink($tempFilePath);
                    }
                    return false;
                }

                if (file_exists($localFilePath)) {
                    unlink($localFilePath);
                }

                rename($tempFilePath, $localFilePath);

                $etagHeader = $response->getHeaderLine('ETag');
                if ($etagHeader) {
                    @file_put_contents($etagPath, $etagHeader);
                }
                return true;
            }
            
            $status = $response->getStatusCode();
            if ($status >= 400) {
                $this->writeNegativeCache($localFilePath);
            }
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
            return false;

        } catch (\Exception $e) {
            if (isset($fileHandle) && is_resource($fileHandle)) {
                fclose($fileHandle);
            }
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
            $this->writeNegativeCache($localFilePath);
            return false;
        }
    }

    /**
     * Compute CDN URL, localPath (public), destinationPath (filesystem) for an imagePath + optional extendedParam.
     */
    private function computePaths(string $imagePath, string $extendedParam = ''): ?array
    {
        $localPath = $this->removeGetParams($imagePath);
        if (!$localPath) {
            return null;
        }
        $cdnUrl = $imagePath;
        $sourcePath = ltrim($localPath, '/');
        $downloadPath = rtrim($this->localCachePath, '/') . '/' . $sourcePath;

        if ($extendedParam) {
            $localPath = $this->appendToImageName($localPath, $extendedParam);
            $sourcePath = ltrim($localPath, '/');
            $destinationPath = rtrim($this->localCachePath, '/') . '/' . $sourcePath;
        } else {
            $destinationPath = $downloadPath;
        }

        return [$cdnUrl, $localPath, $destinationPath];
    }

    /**
     * Quick availability probe for a remote URL. Tries HEAD first, then falls back to Range GET.
     */
    private function probeRemoteAvailable(string $url): bool
    {
        try {
            $client = new Client([
                'timeout' => max(0.001, ($this->strictConnectTimeoutMs + $this->strictReadTimeoutMs) / 1000.0),
                'connect_timeout' => max(0.001, $this->strictConnectTimeoutMs / 1000.0),
                'http_errors' => false,
                'verify' => true,
                'allow_redirects' => [
                    'max' => 3,
                    'track_redirects' => true,
                ],
                'headers' => [
                    'User-Agent' => 'gutes.digital ImageCache/strict',
                    'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                ],
            ]);

            $resp = $client->request('HEAD', $url);
            $code = $resp->getStatusCode();
            if ($code >= 200 && $code < 300) {
                return true;
            }
            // Some CDNs block HEAD; try a tiny ranged GET
            if ($code === 400 || $code === 403 || $code === 405 || $code === 501) {
                $resp2 = $client->request('GET', $url, [
                    'headers' => ['Range' => 'bytes=0-0'],
                ]);
                $c2 = $resp2->getStatusCode();
                if ($c2 === 206 || ($c2 >= 200 && $c2 < 300)) {
                    return true;
                }
            }
            return false;
        } catch (\Throwable $t) {
            return false;
        }
    }

    private function getLockPath(string $destinationPath): string
    {
        return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'imgcache_' . sha1($destinationPath) . '.lock';
    }

    private function tryAcquireLock(?object &$lockHandle, string $lockPath): bool
    {
        $fh = @fopen($lockPath, 'c');
        if ($fh === false) {
            return false;
        }
        if (@flock($fh, LOCK_EX | LOCK_NB)) {
            $lockHandle = $fh;
            return true;
        }
        fclose($fh);
        return false;
    }

    private function releaseLock($lockHandle, string $lockPath): void
    {
        if (is_resource($lockHandle)) {
            @flock($lockHandle, LOCK_UN);
            @fclose($lockHandle);
        }
        @unlink($lockPath);
    }

    private function canDownloadNow(int $localCacheCountLimit): bool
    {
        if ($this->cacheCount >= $localCacheCountLimit) {
            return false;
        }

        $limit = max(1, $this->maxDownloadsPerMinute);

        if (function_exists('apcu_fetch') && ini_get('apc.enabled')) {
            $key = 'imagecache:minute:' . gmdate('YmdHi');
            $cur = apcu_fetch($key);
            if ($cur === false) {
                apcu_add($key, 0, 70); // TTL slightly over a minute
                $cur = 0;
            }
            if ($cur >= $limit) {
                return false;
            }
            apcu_inc($key, 1);
            return true;
        }

        $counterPath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'imgcache_counter_' . gmdate('YmdHi') . '.cnt';
        $count = 0;
        if (file_exists($counterPath)) {
            $raw = @file_get_contents($counterPath);
            $count = (int) $raw;
        }
        if ($count >= $limit) {
            return false;
        }
        @file_put_contents($counterPath, (string)($count + 1), LOCK_EX);
        return true;
    }

    private function refreshInBackground(string $cdnUrl, string $destinationPath, $lockHandle, string $lockPath): void
    {
        try {
            $this->downloadImage($cdnUrl, $destinationPath);
        } finally {
            $this->releaseLock($lockHandle, $lockPath);
        }
    }

    private function getNegativeCachePath(string $localFilePath): string
    {
        return $localFilePath . '.err';
    }

    private function writeNegativeCache(string $localFilePath): void
    {
        $errPath = $this->getNegativeCachePath($localFilePath);
        @file_put_contents($errPath, (string) time());
    }

    private function isNegativelyCached(string $localFilePath): bool
    {
        $errPath = $this->getNegativeCachePath($localFilePath);
        if (!file_exists($errPath)) {
            return false;
        }
        $ts = (int) @file_get_contents($errPath);
        if ($ts <= 0) {
            return false;
        }
        if ((time() - $ts) <= $this->negativeCacheTtl) {
            return true;
        }
        @unlink($errPath);
        return false;
    }

    // Strict variant: respects a shorter TTL to allow quicker recovery when images become available later
    private function isNegativelyCachedStrict(string $localFilePath): bool
    {
        $errPath = $this->getNegativeCachePath($localFilePath);
        if (!file_exists($errPath)) {
            return false;
        }
        $ts = (int) @file_get_contents($errPath);
        if ($ts <= 0) {
            return false;
        }
        $ttl = max(15, min($this->negativeCacheTtl, $this->strictNegativeTtl));
        if ((time() - $ts) <= $ttl) {
            return true;
        }
        @unlink($errPath);
        return false;
    }

    private function getEtagPath(string $localFilePath): string
    {
        return $localFilePath . '.etag';
    }
}