<?php


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