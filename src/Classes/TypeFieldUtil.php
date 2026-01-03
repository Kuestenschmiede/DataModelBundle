<?php
/**
 * This file belongs to gutes.digital and is published exclusively for use
 * in gutes.digital operator or provider pages.

 * @package    gutesio
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design (Matthias Eilers)
 * @link       https://gutes.digital
 */
namespace gutesio\DataModelBundle\Classes;

class TypeFieldUtil
{
    public static function getTypeFieldnames()
    {
        $fieldNames = [];
        $fields = TypeFormFieldGenerator::getAllFields([]);
        foreach ($fields as $field) {
            $fieldNames[] = $field->getName();
        }

        return $fieldNames;
    }
}
