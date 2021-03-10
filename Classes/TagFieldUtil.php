<?php
/**
 * This file belongs to gutes.io and is published exclusively for use
 * in gutes.io operator or provider pages.

 * @package    gutesio
 * @copyright  Küstenschmiede GmbH Software & Design (Matthias Eilers)
 * @link       https://gutes.io
 */
namespace gutesio\DataModelBundle\Classes;

class TagFieldUtil
{
    public static function getTagFieldnames()
    {
        $fieldNames = [];
        \Contao\System::loadLanguageFile('form_tag_fields', 'de');
        $fields = TagFormFieldGenerator::getAllFields();
        foreach ($fields as $field) {
            $fieldNames[] = $field->getName();
        }

        return $fieldNames;
    }
}
