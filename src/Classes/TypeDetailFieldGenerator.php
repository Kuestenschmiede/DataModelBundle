<?php
/**
 * This file belongs to gutes.digital and is published exclusively for use
 * in gutes.digital operator or provider pages.

 * @package    gutesio
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design (Matthias Eilers)
 * @link       https://gutes.digital
 */
namespace gutesio\DataModelBundle\Classes;

use con4gis\FrameworkBundle\Classes\DetailFields\DetailHTMLField;
use con4gis\FrameworkBundle\Classes\DetailFields\DetailLinkField;
use con4gis\FrameworkBundle\Classes\DetailFields\DetailTextAreaField;
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
            case 'type_diet_cuisine':
                return static::getFieldsForDietCuisine();
            case 'type_event_location':
                return static::getFieldsForEventLocation();
            case 'type_lodging':
                return static::getFieldsForLodging();
            case 'type_admission':
                return static::getFieldsForAdmission();
            case 'type_menu':
                return static::getFieldsForMenu();
            case 'type_brochure_upload':
                return static::getFieldsForBrochureUpload();
            case 'type_isbn':
                return static::getFieldsForIsbn();
            case 'type_tour':
                return static::getFieldsForTour();
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
        $field->setName('diet');
        $field->setClass('diet');
        $field->setLabel('Kost');
        $fields[] = $field;

        $field = new DetailTextField();
        $field->setName('cuisine');
        $field->setClass('cuisine');
        $field->setLabel('Küche');
        $fields[] = $field;

        return $fields;
    }

    private static function getFieldsForEventLocation()
    {
        System::loadLanguageFile('form_tag_fields');

        $strName = 'form_tag_fields';

        $fields = [];

        $field = new DetailTextField();
        $field->setName('maxPersons');
        $field->setClass('maxPersons');
        $field->setLabel($GLOBALS['TL_LANG'][$strName]['maxPersons'][0] ? $GLOBALS['TL_LANG'][$strName]['maxPersons'][0] : '');
        $fields[] = $field;

        $field = new DetailHTMLField();
        $field->setName('technicalEquipment');
        $field->setLabel($GLOBALS['TL_LANG'][$strName]['technicalEquipment'][0] ? $GLOBALS['TL_LANG'][$strName]['technicalEquipment'][0] : '');
        $fields[] = $field;

        return $fields;
    }

    private static function getFieldsForLodging()
    {
        System::loadLanguageFile('form_tag_fields');

        $strName = 'form_tag_fields';

        $fields = [];

        $field = new DetailTextField();
        $field->setName('numberOfRooms');
        $field->setClass('numberOfRooms');
        $field->setLabel($GLOBALS['TL_LANG'][$strName]['numberOfRooms'][0] ? $GLOBALS['TL_LANG'][$strName]['numberOfRooms'][0] : '');
        $fields[] = $field;

        $field = new DetailTextField();
        $field->setName('numberOfBeds');
        $field->setClass('numberOfBeds');
        $field->setLabel($GLOBALS['TL_LANG'][$strName]['numberOfBeds'][0] ? $GLOBALS['TL_LANG'][$strName]['numberOfBeds'][0] : '');
        $fields[] = $field;

        $field = new DetailHTMLField();
        $field->setName('equipment');
        $field->setLabel($GLOBALS['TL_LANG'][$strName]['equipment'][0] ? $GLOBALS['TL_LANG'][$strName]['equipment'][0] : '');
        $fields[] = $field;

        return $fields;
    }

    private static function getFieldsForAdmission()
    {
        System::loadLanguageFile('form_tag_fields');

        $strName = 'form_tag_fields';

        $fields = [];

        $field = new DetailHTMLField();
        $field->setName('admissionPrices');
        $field->setLabel($GLOBALS['TL_LANG'][$strName]['admissionPrices'][0] ? $GLOBALS['TL_LANG'][$strName]['admissionPrices'][0] : '');
        $fields[] = $field;

        return $fields;
    }

    private static function getFieldsForMenu()
    {
        $fields = [];

        $field = new DetailLinkField();
        $field->setName(TypeFormFieldGenerator::FIELD_MENU_LINK);
        $field->setLabel('Speisekarte (Link)');
        $field->setLinkText('Speisekarte');
        $field->setClass(TypeFormFieldGenerator::FIELD_MENU_LINK);
        $field->setTargetBlank(true);
        $fields[] = $field;

        $field = new PDFDetailField();
        $field->setName(TypeFormFieldGenerator::FIELD_MENU_UPLOAD);
        $field->setLabel('Speisekarte');
        $field->setTitle('Speisekarte öffnen');
        $field->setClass(TypeFormFieldGenerator::FIELD_MENU_UPLOAD);
        $fields[] = $field;

        return $fields;
    }

    private static function getFieldsForBrochureUpload()
    {
        $fields = [];

        $field = new PDFDetailField();
        $field->setName(TypeFormFieldGenerator::FIELD_BROCHURE_UPLOAD);
        $field->setLabel('Broschüre');
        $field->setTitle('Broschüre öffnen');
        $field->setClass(TypeFormFieldGenerator::FIELD_BROCHURE_UPLOAD);
        $fields[] = $field;

        return $fields;
    }

    private static function getFieldsForIsbn()
    {
        $field = new DetailTextField();
        $field->setName(TypeFormFieldGenerator::FIELD_ISBN);
        $field->setLabel('ISBN:');
        $field->setClass(TypeFormFieldGenerator::FIELD_ISBN);

        return [$field];
    }

    private static function getFieldsForTour()
    {
        System::loadLanguageFile('form_tag_fields');

        $strName = 'form_tag_fields';

        $fields = [];

        $field = new DetailTextField();
        $field->setName('duration');
        $field->setClass('duration');
        $field->setLabel($GLOBALS['TL_LANG'][$strName]['duration'][0] ? $GLOBALS['TL_LANG'][$strName]['duration'][0] : '');
        $fields[] = $field;

        $field = new DetailTextField();
        $field->setName('length');
        $field->setClass('length');
        $field->setLabel($GLOBALS['TL_LANG'][$strName]['length'][0] ? $GLOBALS['TL_LANG'][$strName]['length'][0] : '');
        $fields[] = $field;

        $field = new DetailTextField();
        $field->setName('elevationMin');
        $field->setClass('elevationMin');
        $field->setLabel($GLOBALS['TL_LANG'][$strName]['elevationMin'][0] ? $GLOBALS['TL_LANG'][$strName]['elevationMin'][0] : '');
        $fields[] = $field;

        $field = new DetailTextField();
        $field->setName('elevationMax');
        $field->setClass('elevationMax');
        $field->setLabel($GLOBALS['TL_LANG'][$strName]['elevationMax'][0] ? $GLOBALS['TL_LANG'][$strName]['elevationMax'][0] : '');
        $fields[] = $field;

        $field = new DetailTextField();
        $field->setName('totalAscent');
        $field->setClass('totalAscent');
        $field->setLabel($GLOBALS['TL_LANG'][$strName]['totalAscent'][0] ? $GLOBALS['TL_LANG'][$strName]['totalAscent'][0] : '');
        $fields[] = $field;

        $field = new DetailTextField();
        $field->setName('totalDescent');
        $field->setClass('totalDescent');
        $field->setLabel($GLOBALS['TL_LANG'][$strName]['totalDescent'][0] ? $GLOBALS['TL_LANG'][$strName]['totalDescent'][0] : '');
        $fields[] = $field;

        $field = new DetailTextField();
        $field->setName('roundTrip');
        $field->setClass('roundTrip');
        $field->setLabel($GLOBALS['TL_LANG'][$strName]['roundTrip'][0] ? $GLOBALS['TL_LANG'][$strName]['roundTrip'][0] : '');
        $fields[] = $field;

        return $fields;
    }
}
