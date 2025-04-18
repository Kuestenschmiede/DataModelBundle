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

    //default 4hm nax. 10 new images
    public function getImage(string $imagePath, int $time=14400, int $cacheCount=10): string
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
                $this->downloadImage($cdnUrl, $destinationPath);
                $this->cacheCount++;
            } else {
                return $imagePath;
            }
        }

        return $this->localPublicPath . $localPath;
    }

    //default 4h, max. 5000 images
    public function getImages(array $imagePaths, int $time=14400, int $cacheCount=5000): bool
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
                    //$sourcePaths[] = $cdnUrl;
                    //$destinationPaths[] = $destinationPath;
                    $this->downloadImage($cdnUrl, $destinationPath);
                    $this->cacheCount++;
                }
            }
        }

//        if (count($sourcePaths) > 0 && count($destinationPaths) > 0) {
//            $this->downloadImages($sourcePaths, $destinationPaths);
//        }

        return true;
    }

    //default 4h
    private function isCacheExpired(string $filePath, int $time=14400): bool
    {
        if (!file_exists($filePath)) {
            return true;
        }

        $fileTimestamp = filemtime($filePath);
        return (time() - $fileTimestamp) > $time;
    }

//    private function downloadImages(array $urls, array $localFilePaths): void
//    {
//        $client = new Client();
//        $promises = [];
//
//        foreach ($urls as $index => $url) {
//            if (!isset($promises[$url])) {
//                $destinationDir = dirname($localFilePaths[$index]);
//
//                if (!is_dir($destinationDir) && !mkdir($destinationDir, 0777, true) && !is_dir($destinationDir)) {
//                    throw new \RuntimeException(sprintf('Verzeichnis "%s" konnte nicht erstellt werden.', $destinationDir));
//                }
//
//                // Asynchrone GET-Anfrage hinzufügen (einmalig pro URL)
//                $promises[$url] = $client->getAsync($url, ['sink' => $localFilePaths[$index]]);
//            }
//        }
//
//        // Sicherstellen, dass keine Promise doppelt verarbeitet wird
//        $results = \GuzzleHttp\Promise\Utils::settle($promises)->wait();
//
////        foreach ($results as $index => $result) {
////            if ($result['state'] === 'rejected') {
////                $reason = $result['reason'];
////                error_log("Download fehlgeschlagen für URL {$urls[$index]}: {$reason->getMessage()}");
////            }
////        }
//    }
    
    private function downloadImage(string $url, string $localFilePath): void
    {
        $client = new Client();
        $destinationDir = dirname($localFilePath);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0777, true);
        }

        try {
            $response = $client->head($url);
            $lastModified = $response->getHeaderLine('Last-Modified');

            if (file_exists($localFilePath) && $lastModified && filemtime($localFilePath) >= strtotime($lastModified)) {
                return;
            }

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