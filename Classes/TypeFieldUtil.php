<?php
/**
 * This file belongs to gutes.io and is published exclusively for use
 * in gutes.io operator or provider pages.

 * @package    gutesio
 * @copyright  Küstenschmiede GmbH Software & Design (Matthias Eilers)
 * @link       https://gutes.io
 */
namespace gutesio\DataModelBundle\Classes;

class TypeFieldUtil
{
    public static function getTypeFieldnames()
    {
        $fieldNames = [];
        $fields = TypeFormFieldGenerator::getAllFields();
        foreach ($fields as $field) {
            $fieldNames[] = $field->getName();
        }

        return $fieldNames;
    }
}
