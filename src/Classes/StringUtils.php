<?php

namespace gutesio\DataModelBundle\Classes;

class StringUtils
{
    public static function addUrlToPath($url, $path, $cropWidth = 0, $cropHeight = 0)
    {
        $result = $path;
        if ($url && $path && strpos(strtolower($path), 'http') === false) {
            $result = $url.$path;
        }

        if ($cropWidth && $cropHeight) {
            $result .= "?crop=smart&width=".$cropWidth."&height=".$cropHeight;
        }

        return $result;
    }

}