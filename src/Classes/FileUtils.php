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

        //if (strpos($url, 'con4gis') !== false) {
            if ($cropWidth && $cropHeight) {
                $result .= "?crop=smart&width=".$cropWidth."&height=".$cropHeight;
            } else if ($cropWidth) {
                $result .= "?crop=smart&width=".$cropWidth;
            } else if ($cropHeight) {
                $result .= "?crop=smart&height=".$cropHeight;
            }
        //}

        return $result;
    }

    //caching 4h
    public function addUrlToPathAndGetImage($url, $path, $cropWidth = 0, $cropHeight = 0, $time=14400) {
        $result = $this->addUrlToPath($url, $path, $cropWidth, $cropHeight);
        return $this->getImage($result, $time);
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
    public function getImage($imagePath, $time=14400) {
        try {
            $localImage = $this->imageCache->getImage($imagePath, $time);
            return $localImage;
        } catch (\Exception $e) {
            C4gLogModel::addLogEntry("operator", "Fehler beim Abrufen des Bildes: " . $e->getMessage());
        }

        return $imagePath;
    }
}