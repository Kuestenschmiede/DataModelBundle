<?php


namespace gutesio\DataModelBundle\Classes;


class TagFieldUtil
{
    public static function getTagFieldnames()
    {
        $fieldNames = [];
        \System::loadLanguageFile("form_tag_fields", "de");
        $fields = TagFormFieldGenerator::getAllFields();
        foreach ($fields as $field) {
            $fieldNames[] = $field->getName();
        }
    
        return $fieldNames;
    }
}