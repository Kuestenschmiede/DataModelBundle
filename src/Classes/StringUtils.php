<?php

namespace gutesio\DataModelBundle\Classes;

class StringUtils
{
    public static function addUrlToPath($url, $path)
    {
        $result = $path;
        if ($url && $path && strpos(strtolower($path), 'http') === false) {
            $result = $url.$path;
        }

        return $result;
    }

}