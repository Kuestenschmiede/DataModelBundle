<?php
/**
 * This file belongs to gutes.digital and is published exclusively for use
 * in gutes.digital operator or provider pages.

 * @package    gutesio
 * @copyright  Küstenschmiede GmbH Software & Design (Matthias Eilers)
 * @link       https://gutes.digital
 */
namespace gutesio\DataModelBundle\Classes;

use con4gis\FrameworkBundle\Classes\FormFields\CKEditorFormField;
use con4gis\FrameworkBundle\Classes\FormFields\TextFormField;

/**
 * Class TagFormFieldGenerator
 * Class for loading additional form fields for tags by their technical keys.
 * @package gutesio\DataModelBundle\Classes
 */
class TagFormFieldGenerator
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
            case 'tag_help_support':
                return static::createFieldForHelpSupport();
            case 'tag_discussion_forum':
                return static::createFieldForDiscussionForum();
            case 'tag_ios_app':
                return static::createFieldForIOSApp();
            case 'tag_android_app':
                return static::createFieldForAndroidApp();
            case 'tag_online_counseling':
                return static::createFieldForOnlineCounseling();
            case 'tag_online_chat':
                return static::createFieldForOnlineChat();
            case 'tag_online_video_forum':
                return static::createFieldForOnlineVideoForum();
            case 'tag_online_therapy_program':
                return static::createFieldForOnlineTherapyProgram();
            case 'tag_tariff_calculator':
                return static::createFieldForTariffCalculator();
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

    public static function getNonMatchingFields(array $technicalKeys)
    {
        $allFields = static::getAllFields();
        $matchingFields = static::getFieldsForTags($technicalKeys);
        $nonMatchingFields = [];
        foreach ($allFields as $field) {
            $addField = true;
            foreach ($matchingFields as $matchingField) {
                if ($matchingField->getName() === $field->getName()) {
                    $addField = false;

                    break;
                }
            }
            if ($addField) {
                $nonMatchingFields[] = $field;
            }
        }

        return $nonMatchingFields;
    }

    public static function getAllFields()
    {
        $deliveryFields = static::createFieldForDeliveryTag();
        $wheelChairFields = static::createFieldForWheelchairTag();
        $coronaFields = static::createFieldForCoronaTag();
        $reservationFields = static::createFieldForOnlineReservationTag();
        $onlineShopFields = static::createFieldForOnlineshopTag();
        $michelinFields = static::createFieldForMichelinStarsTag();

        return array_merge(
            $deliveryFields,
            $wheelChairFields,
            $coronaFields,
            $reservationFields,
            $onlineShopFields,
            $michelinFields
        );
    }

    private static function createFieldForDeliveryTag()
    {
        $fields = [];
        $field = new TextFormField();
        $field->setName('deliveryServiceLink');
        $field->setLabel($GLOBALS['TL_LANG']['form_tag_fields']['deliveryServiceLink'][0]);
        $fields[] = $field;

        return $fields;
    }

    private static function createFieldForWheelchairTag()
    {
        $fields = [];
        $field = new CKEditorFormField();
        $field->setName('wheelchairNotes');
        $field->setLabel($GLOBALS['TL_LANG']['form_tag_fields']['wheelchairNotes'][0]);
        $field->setDescription($GLOBALS['TL_LANG']['form_tag_fields']['wheelchairNotes'][1]);
        $field->setParagraphLabel($GLOBALS['TL_LANG']['form_tag_fields']['frontend']['paragraph']);
        $field->setHeadingLabel($GLOBALS['TL_LANG']['form_tag_fields']['frontend']['heading']);
        $fields[] = $field;

        return $fields;
    }

    private static function createFieldForCoronaTag()
    {
        $fields = [];
        $field = new CKEditorFormField();
        $field->setName('coronaNotes');
        $field->setLabel($GLOBALS['TL_LANG']['form_tag_fields']['coronaNotes'][0]);
        $field->setDescription($GLOBALS['TL_LANG']['form_tag_fields']['coronaNotes'][1]);
        $field->setParagraphLabel($GLOBALS['TL_LANG']['form_tag_fields']['frontend']['paragraph']);
        $field->setHeadingLabel($GLOBALS['TL_LANG']['form_tag_fields']['frontend']['heading']);
        $fields[] = $field;

        return $fields;
    }

    private static function createFieldForOnlineReservationTag()
    {
        $fields = [];
        $field = new TextFormField();
        $field->setName('onlineReservationLink');
        $field->setLabel($GLOBALS['TL_LANG']['form_tag_fields']['onlineReservationLink'][0]);
        $field->setDescription($GLOBALS['TL_LANG']['form_tag_fields']['onlineReservationLink'][1]);
        $fields[] = $field;

        return $fields;
    }

    private static function createFieldForClicknmeetTag()
    {
        $fields = [];
        $field = new TextFormField();
        $field->setName('clicknmeetLink');
        $field->setLabel($GLOBALS['TL_LANG']['form_tag_fields']['clicknmeetLink'][0]);
        $field->setDescription($GLOBALS['TL_LANG']['form_tag_fields']['clicknmeetLink'][1]);
        $fields[] = $field;

        return $fields;
    }

    private static function createFieldForTableReservationTag()
    {
        $fields = [];
        $field = new TextFormField();
        $field->setName('tableReservationLink');
        $field->setLabel($GLOBALS['TL_LANG']['form_tag_fields']['tableReservationLink'][0]);
        $field->setDescription($GLOBALS['TL_LANG']['form_tag_fields']['tableReservationLink'][1]);
        $fields[] = $field;

        return $fields;
    }

    private static function createFieldForOnlineshopTag()
    {
        $fields = [];
        $field = new TextFormField();
        $field->setName('onlineShopLink');
        $field->setLabel($GLOBALS['TL_LANG']['form_tag_fields']['onlineShopLink'][0]);
        $field->setDescription($GLOBALS['TL_LANG']['form_tag_fields']['onlineShopLink'][1]);
        $fields[] = $field;

        return $fields;
    }

    private static function createFieldForMichelinStarsTag()
    {
        $fields = [];
        $field = new TextFormField();
        $field->setName('michelinStars');
        $field->setLabel($GLOBALS['TL_LANG']['form_tag_fields']['michelinStars'][0]);
        $field->setDescription($GLOBALS['TL_LANG']['form_tag_fields']['michelinStars'][1]);
        $fields[] = $field;

        return $fields;
    }

    private static function createFieldForHelpSupport()
    {
        return static::createLinkField('helpSupport', 'helpSupport');
    }

    private static function createFieldForDiscussionForum()
    {
        return static::createLinkField('discussionForum', 'discussionForum');
    }

    private static function createFieldForIOSApp()
    {
        return static::createLinkField('iosApp', 'iosApp');
    }

    private static function createFieldForAndroidApp()
    {
        return static::createLinkField('androidApp', 'androidApp');
    }

    private static function createFieldForOnlineCounseling()
    {
        return static::createLinkField('onlineCounseling', 'onlineCounseling');
    }

    private static function createFieldForOnlineChat()
    {
        return static::createLinkField('onlineChat', 'onlineChat');
    }

    private static function createFieldForOnlineVideoForum()
    {
        return static::createLinkField('onlineVideoForum', 'onlineVideoForum');
    }

    private static function createFieldForOnlineTherapyProgram()
    {
        return static::createLinkField('onlineTherapyProgram', 'onlineTherapyProgram');
    }

    private static function createFieldForTariffCalculator()
    {
        return static::createLinkField('tariffCalculator', 'tariffCalculator');
    }

    private static function createLinkField($fieldName, $langKey)
    {
        $fields = [];
        $field = new TextFormField();
        $field->setName($fieldName);
        $field->setLabel($GLOBALS['TL_LANG']['form_tag_fields'][$langKey][0]);
        $fields[] = $field;

        return $fields;
    }
}
