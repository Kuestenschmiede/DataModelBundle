<?php
/**
 * This file belongs to gutes.digital and is published exclusively for use
 * in gutes.digital operator or provider pages.

 * @package    gutesio
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design (Matthias Eilers)
 * @link       https://gutes.digital
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

    public static function getFieldnameForTechnicalKey($key)
    {
        // currently only one field per tag
        $tagFields = TagDetailFieldGenerator::getFieldsForTag($key);
        $tagField = $tagFields[0];

        return $tagField ? $tagField->getName() : '';
    }
}
