<?php

namespace gutesio\DataModelBundle\Classes;

class DCAUtil
{
    public static function merge(array &$old, array $new) {
        $oldFields = $old['fields'];
        $old = $new;
        foreach ($oldFields as $key => $field) {
            $old['fields'][$key]['sql'] = $field['sql'];
        }
    }
}