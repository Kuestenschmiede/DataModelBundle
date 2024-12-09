<?php

namespace gutesio\DataModelBundle\Classes;

use FastImageSize\FastImageSize;

class FileUtils
{
    public static function addUrlToPath($url, $path, $cropWidth = 0, $cropHeight = 0)
    {
        $result = $path;
        if ($url && $path && strpos(strtolower($path), 'http') === false) {
            $result = $url.$path;
        }

        if ($cropWidth && $cropHeight) {
            $result .= "?crop=smart&width=".$cropWidth."&height=".$cropHeight;
        } else if ($cropWidth) {
            $result .= "?crop=smart&width=".$cropWidth;
        } else if ($cropHeight) {
            $result .= "?crop=smart&height=".$cropHeight;
        }

        return $result;
    }

    public static function getImageSize($uri)
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

    public static function getImageOrientation($size) {
        list($width, $height) = $size;
        $orientation = ( $width != $height ? ( $width > $height ? 'landscape' : 'portrait' ) : 'square' );
        return $orientation;
    }

    public static function getImageSizeAndOrientation($uri) {
        $size = self::getImageSize($uri);
        $orientation = self::getImageOrientation($size);
        return [$size, $orientation];
    }
}