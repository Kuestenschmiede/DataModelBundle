<?php
/**
 * This file belongs to gutes.digital and is published exclusively for use
 * in gutes.digital operator or provider pages.

 * @package    gutesio
 * @copyright  Küstenschmiede GmbH Software & Design (Matthias Eilers)
 * @link       https://gutes.digital
 */
namespace gutesio\DataModelBundle\Classes;

use con4gis\FrameworkBundle\Classes\DetailFields\DetailHTMLField;
use con4gis\FrameworkBundle\Classes\DetailFields\DetailLinkField;
use con4gis\FrameworkBundle\Classes\DetailFields\DetailTextField;

/**
 * Class TagDetailFieldGenerator
 * Class for loading additional detail fields for tags by their technical keys.
 * @package gutesio\DataModelBundle\Classes
 */
class TagDetailFieldGenerator
{
    public static function getFieldsForTag(string $technicalKey)
    {
        switch ($technicalKey) {
            case 'tag_delivery':
                return static::createFieldForDeliveryTag();
            case 'tag_wheelchair':
                return static::createFieldForWheelchairTag();
            case 'tag_corona':
                return static::createFieldForCoronaTag();
            case 'tag_online_reservation':
                return static::createFieldForOnlineReservationTag();
            case 'tag_onlineshop':
                return static::createFieldForOnlineshopTag();
            case 'tag_michelin_stars':
                return static::createFieldForMichelinStarsTag();
            case 'tag_donation':
                return static::createFieldForDonationTag();
            default:
                return [];
        }
    }

    public static function getFieldsForTags(array $technicalKeys)
    {
        $fields = [];
        foreach ($technicalKeys as $technicalKey) {
            $fields = array_merge($fields, static::getFieldsForTag($technicalKey));
        }

        return $fields;
    }

    private static function createFieldForDeliveryTag()
    {
        $fields = [];

        $field = new DetailLinkField();
        $field->setName('deliveryServiceLink'); // ToDo oder muss weiterhin delivery_conditions heißen?
        $field->setLabel('Lieferbedingungen');
        $field->setLinkText('Lieferservice');
        //$field->setLinkTextName('deliveryServiceLinkLabel');
        $fields[] = $field;

        return $fields;
    }

    private static function createFieldForWheelchairTag()
    {
        $fields = [];

        $field = new DetailHTMLField();
        $field->setName('wheelchairNotes'); // ToDo
        $field->setLabel('Hinweise zur Rollstuhleignung');
        $fields[] = $field;

        return $fields;
    }

    private static function createFieldForCoronaTag()
    {
        $fields = [];

        $field = new DetailHTMLField();
        $field->setName('coronaNotes'); // ToDo
        $field->setLabel('Hinweise zur Corona Situation');
        $fields[] = $field;

        return $fields;
    }

//    private static function createFieldForMenuTag()
//    {
//        $fields = [];
//
//        $field = new DetailLinkField();
//        $field->setName('menuLink'); // ToDo
//        $field->setLabel("Link zur Speisekarte");
//        $field->setLinkText("Speisekarte");
//        //$field->setLinkTextName('menuLinkLabel');
//        $fields[] = $field;
//
//        return $fields;
//    }

    private static function createFieldForOnlineReservationTag()
    {
        $fields = [];
        $field = new DetailLinkField();
        $field->setName('onlineReservationLink'); // ToDo
        $field->setLabel('Link zur Reservierung');
        $field->setLinkText('Onlinereservierung');
        //$field->setLinkTextName("onlineReservationLinkLabel");
        $fields[] = $field;

        return $fields;
    }

    private static function createFieldForClicknmeetTag()
    {
        $fields = [];
        $field = new DetailLinkField();
        $field->setName('clicknmeetLink'); // ToDo
        $field->setLabel('Link zur Terminreservierung');
        $field->setLinkText('Click & Meet');
        $fields[] = $field;

        return $fields;
    }

    private static function createFieldForTableReservationTag()
    {
        $fields = [];
        $field = new DetailLinkField();
        $field->setName('tableReservationLink'); // ToDo
        $field->setLabel('Link zur Tischreservierung');
        $field->setLinkText('Tischreservierung');
        $fields[] = $field;

        return $fields;
    }

    private static function createFieldForOnlineshopTag()
    {
        $fields = [];
        $field = new DetailLinkField();
        $field->setName('onlineShopLink'); // ToDo
        $field->setLabel('Link zum Onlineshop');
        $field->setLinkText('Onlineshop');
        //$field->setLinkTextName("onlineShopLinkLabel");
        $fields[] = $field;

        return $fields;
    }

    private static function createFieldForMichelinStarsTag()
    {
        $fields = [];

        $field = new DetailTextField();
        $field->setName('michelinStars'); // ToDo
        $field->setLabel('Michelin-Sterne');
        $fields[] = $field;

        return $fields;
    }
}
