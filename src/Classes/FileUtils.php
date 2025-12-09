<?php

namespace gutesio\DataModelBundle\Classes;

use con4gis\CoreBundle\Resources\contao\models\C4gLogModel;
use FastImageSize\FastImageSize;
use gutesio\DataModelBundle\Classes\Cache\ImageCache;

class FileUtils
{
    private $imageCache = null;

    public function __construct()
    {
        $url = (empty($_SERVER['HTTPS'])) ? 'http://' : 'https://';
        $url .= $_SERVER['HTTP_HOST'];
        $rootDir = \Contao\System::getContainer()->getParameter('kernel.project_dir');
        $localCachePath = $rootDir.'/files/con4gis_import_data/images';
        $localPublicPath = $url.'/files/con4gis_import_data/images';

        $this->imageCache = new ImageCache($localCachePath, $localPublicPath);
    }

    public function addUrlToPath($url, $path, $cropWidth = 0, $cropHeight = 0)
    {
        $result = $path;
        if ($url && $path && strpos(strtolower($path), 'http') === false) {
            $result = $url.$path;
        }

        if ($cropWidth && $cropHeight) {
            $result .= "?crop=smart&width=".$cropWidth."&height=".$cropHeight."&quality=85&format=webp&fit=scale-down";
        } else if ($cropWidth) {
            $result .= "?crop=smart&width=".$cropWidth."&quality=85&format=webp&fit=scale-down";
        } else if ($cropHeight) {
            $result .= "?crop=smart&height=".$cropHeight."&quality=85&format=webp&fit=scale-down";
        }

        return $result;
    }

    //caching 4h
    public function addUrlToPathAndGetImage($url, $path, $extendedParam = '', $cropWidth = 0, $cropHeight = 0, $time=172800, $ignoreExpiry = false) {
        if (strpos($path, 'http') !== false) {
            return $path;
        }
        $result = $this->addUrlToPath($url, $path, $cropWidth, $cropHeight);
        return $this->getImage($result, $extendedParam, $time, $ignoreExpiry);
    }

    public function getImageSize($uri)
    {
        $size = [0, 0];
        try {
            if ($uri) {
                $fastImageSize = new FastImageSize();
                $size = $fastImageSize->getImageSize($uri);
                $size = $size ? [$size['width'], $size['height']] : [0, 0];
            }
        } catch (\Exception $exception) {
            $size = [0, 0];
        } catch (\Throwable $exception) {
            $size = [0, 0];
        }
        return $size;
    }

    public function getImageOrientation($size) {
        list($width, $height) = $size;
        $orientation = ( $width != $height ? ( $width > $height ? 'landscape' : 'portrait' ) : 'square' );
        return $orientation;
    }

    public function getImageSizeAndOrientation($uri) {
        $size = $this->getImageSize($uri);
        $orientation = $this->getImageOrientation($size);
        return [$size, $orientation];
    }

    //caching 4h
    public function getImage($imagePath, $extendedParam = '', $time=172800, $ignoreExpiry = false) {
        try {
            $localImage = $this->imageCache->getImage($imagePath, $extendedParam, $time, 4, $ignoreExpiry);
            return $localImage;
        } catch (\Exception $e) {
            C4gLogModel::addLogEntry("operator", "Fehler beim Abrufen des Bildes: " . $e->getMessage());
        }

        return $imagePath;
    }

    public function getImages(Array $files, $time=172800) {
        $this->imageCache->getImages($files, $time);
    }
}