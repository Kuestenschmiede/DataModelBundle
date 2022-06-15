<?php
/**
 * This file belongs to gutes.digital and is published exclusively for use
 * in gutes.digital operator or provider pages.

 * @package    gutesio
 * @copyright  Küstenschmiede GmbH Software & Design (Matthias Eilers)
 * @link       https://gutes.digital
 */
namespace gutesio\DataModelBundle\Classes;

class DCAUtil
{
    public static function merge(array &$old, array $new)
    {
        $oldFields = $old['fields'];
        $old = $new;
        foreach ($oldFields as $key => $field) {
            $old['fields'][$key]['sql'] = $field['sql'];
        }
    }
}
