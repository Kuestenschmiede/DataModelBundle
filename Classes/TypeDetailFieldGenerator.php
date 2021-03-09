<?php
/**
 * This file is part of con4gis,
 * the gis-kit for Contao CMS.
 *
 * @package   	con4gis
 * @version        6
 * @author  	    con4gis contributors (see "authors.txt")
 * @license 	    LGPL-3.0-or-later
 * @copyright 	Küstenschmiede GmbH Software & Design
 * @link              https://www.con4gis.org
 *
 */

namespace gutesio\DataModelBundle\Classes;

use con4gis\FrameworkBundle\Classes\DetailFields\DetailLinkField;
use con4gis\FrameworkBundle\Classes\DetailFields\DetailTagField;
use con4gis\FrameworkBundle\Classes\DetailFields\DetailTextField;
use con4gis\FrameworkBundle\Classes\DetailFields\PDFDetailField;
use con4gis\FrameworkBundle\Classes\Utility\FieldUtil;
use Contao\System;

/**
 * Class TypeFormFieldGenerator
 * Class for loading additional detail fields for types by their technical keys.
 * @package gutesio\DataModelBundle\Classes
 */
class TypeDetailFieldGenerator
{
    public static function getFieldsForType(string $technicalKey)
    {
        switch ($technicalKey) {
            case "type_diet_cuisine":
                return static::getFieldsForDietCuisine();
            case "type_event_location":
                return static::getFieldsForEventLocation();
            case "type_menu":
                return static::getFieldsForMenu();
            case "type_brochure_upload":
                return static::getFieldsForBrochureUpload();
            default:
                return [];
        }
    }
    
    public static function getFieldsForTypes(array $technicalKeys)
    {
        $fields = [];
        foreach ($technicalKeys as $technicalKey) {
            $fields = array_merge($fields, static::getFieldsForType($technicalKey));
        }
        
        return FieldUtil::makeFieldArrayUnique($fields);
    }
    
    private static function getFieldsForDietCuisine()
    {
        $fields = [];
        
        $field = new DetailTextField();
        $field->setName("diet");
        $field->setClass("diet");
        $field->setLabel("Kost");
        $fields[] = $field;
        
        $field = new DetailTextField();
        $field->setName("cuisine");
        $field->setClass("cuisine");
        $field->setLabel("Küche");
        $fields[] = $field;
        
        return $fields;
    }
    
    private static function getFieldsForEventLocation()
    {
        System::loadLanguageFile("form_tag_fields");

        $strName = "form_tag_fields";
        
        $fields = [];
        
        $field = new DetailTextField();
        $field->setName("maxPersons");
        $field->setClass("maxPersons");
        $field->setLabel($GLOBALS['TL_LANG'][$strName]['maxPersons'][0] ? $GLOBALS['TL_LANG'][$strName]['maxPersons'][0] : '');
        $fields[] = $field;
        
        $field = new DetailTextField();
        $field->setName("technicalEquipment");
        $field->setClass("technicalEquipment");
        $field->setLabel($GLOBALS['TL_LANG'][$strName]['technicalEquipment'][0] ? $GLOBALS['TL_LANG'][$strName]['technicalEquipment'][0] : '');
        $fields[] = $field;
        
        return $fields;
    }
    
    private static function getFieldsForMenu()
    {
        $fields = [];

        $field = new DetailLinkField();
        $field->setName(TypeFormFieldGenerator::FIELD_MENU_LINK);
        $field->setLabel("Speisekarte (Link)");
        $field->setLinkText("Speisekarte");
        $field->setClass(TypeFormFieldGenerator::FIELD_MENU_LINK);
        $fields[] = $field;

        $field = new PDFDetailField();
        $field->setName(TypeFormFieldGenerator::FIELD_MENU_UPLOAD);
        $field->setLabel("Speisekarte");
        $field->setTitle("Speisekarte öffnen");
        $field->setClass(TypeFormFieldGenerator::FIELD_MENU_UPLOAD);
        $fields[] = $field;
    
        return $fields;
    }
    
    private static function getFieldsForBrochureUpload()
    {
        $fields = [];

        $field = new PDFDetailField();
        $field->setName(TypeFormFieldGenerator::FIELD_BROCHURE_UPLOAD);
        $field->setLabel("Broschüre");
        $field->setTitle("Broschüre öffnen");
        $field->setClass(TypeFormFieldGenerator::FIELD_BROCHURE_UPLOAD);
        $fields[] = $field;
    
        return $fields;
    }
}