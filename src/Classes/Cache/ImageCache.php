<?php

namespace gutesio\DataModelBundle\Classes\Cache;

use GuzzleHttp\Client;

class ImageCache
{
    private $localCachePath;
    private $localPublicPath;
    private $cacheCount = 0;

    public function __construct(string $localCachePath, string $localPublicPath)
    {
        $this->localCachePath = rtrim($localCachePath, '/');
        $this->localPublicPath = rtrim($localPublicPath, '/');

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
                if (($now - $fileModifiedTime) > (2 * 86400)) {
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
        return $urlParts['path'];
    }

    public function getImage(string $imagePath): string
    {
        $localPath = $this->removeGetParams($imagePath);
        $cdnUrl = $imagePath;

        $parsedUrl = parse_url($localPath);
        if (!isset($parsedUrl['path'])) {
            return false;
        }

        $sourcePath = ltrim($parsedUrl['path'], '/');
        $destinationPath = rtrim($this->localCachePath, '/') . '/' . $sourcePath;

        if ($this->isCacheExpired($destinationPath)) {
            if ($this->cacheCount < 10) {
                $this->downloadImage($cdnUrl, $destinationPath);
                $this->cacheCount++;
            } else {
                return $imagePath;
            }
        }

        return $this->localPublicPath . $localPath;
    }

    private function isCacheExpired(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return true;
        }

        $fileTimestamp = filemtime($filePath);
        return (time() - $fileTimestamp) > 86400;
    }

    private function downloadImage(string $url, string $localFilePath): void
    {
        $client = new Client();

        $destinationDir = dirname($localFilePath);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0777, true);
        }

        try {
            $response = $client->get($url, ['sink' => $localFilePath]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception("Failed to download image: " . $url);
            }
        } catch (\Exception $e) {
            if (file_exists($localFilePath)) {
                unlink($localFilePath);
            }

            throw $e;
        }
    }
}