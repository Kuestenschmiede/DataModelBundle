<?php

namespace gutesio\DataModelBundle\Classes\Cache;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;

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

    //default 48h nax. 4 new images
    public function getImage(string $imagePath, int $time=172800, int $cacheCount=4): string
    {
        $localPath = $this->removeGetParams($imagePath);
        if (!$localPath) {
            return false;
        }

        $cdnUrl = $imagePath;
        $sourcePath = ltrim($localPath, '/');
        $destinationPath = rtrim($this->localCachePath, '/') . '/' . $sourcePath;

        if ($this->isCacheExpired($destinationPath, $time)) {
            if ($this->cacheCount < $cacheCount) {
                if (!$this->downloadImage($cdnUrl, $destinationPath)) {
                    return $imagePath;
                };
                $this->cacheCount++;
            } else {
                return $imagePath;
            }
        }

        return $this->localPublicPath . $localPath;
    }

    //default 48h, max. 5000 images
    public function getImages(array $imagePaths, int $time=172800, int $cacheCount=5000): bool
    {
        $sourcePaths = [];
        $destinationPaths = [];
        foreach ($imagePaths as $imagePath) {
            $localPath = $this->removeGetParams($imagePath);
            if (!$localPath) {
                continue;
            }
            $cdnUrl = $imagePath;

            $sourcePath = ltrim($localPath, '/');
            $destinationPath = rtrim($this->localCachePath, '/') . '/' . $sourcePath;

            if ($this->isCacheExpired($destinationPath, $time)) {
                if ($this->cacheCount < $cacheCount) {
                    $this->downloadImage($cdnUrl, $destinationPath);
                    $this->cacheCount++;
                }
            }
        }

        return true;
    }

    //default 48h
    private function isCacheExpired(string $filePath, int $time=172800): bool
    {
        if (!file_exists($filePath)) {
            return true;
        }

        $fileTimestamp = filemtime($filePath);
        return (time() - $fileTimestamp) > $time;
    }

    private function downloadImage(string $url, string $localFilePath): bool
    {
        $client = new Client([
            'timeout' => 10,
            'connect_timeout' => 5,
            'http_errors' => false,
            'verify' => false
        ]);

        $destinationDir = dirname($localFilePath);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0777, true);
        }

        try {
            $fileHandle = fopen($localFilePath, 'w');
            $response = $client->get($url, ['sink' => $fileHandle]);

            if ($response->getStatusCode() !== 200) {
                fclose($fileHandle);
                if (file_exists($localFilePath)) {
                    unlink($localFilePath);
                }
                return false;
            }

            fclose($fileHandle);
            return true;

        } catch (\Exception $e) {
            if (isset($fileHandle) && is_resource($fileHandle)) {
                fclose($fileHandle);
            }
            if (file_exists($localFilePath)) {
                unlink($localFilePath);
            }
            return false;
        }
    }


}