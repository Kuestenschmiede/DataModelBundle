<?php
/**
 * This file belongs to gutes.digital and is published exclusively for use
 * in gutes.digital operator or provider pages.

 * @package    gutesio
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design (Matthias Eilers)
 * @link       https://gutes.digital
 */
namespace gutesio\DataModelBundle\Classes;

use con4gis\FrameworkBundle\Classes\FormFields\CKEditorFormField;
use con4gis\FrameworkBundle\Classes\FormFields\SelectFormField;
use con4gis\FrameworkBundle\Classes\FormFields\TextFormField;
use con4gis\ReservationBundle\Classes\Models\C4gReservationSettingsModel;
use gutesio\DataModelBundle\Resources\contao\models\GutesioDataTypeModel;

/**
 * Class TagFormFieldGenerator
 * Class for loading additional form fields for tags by their technical keys.
 * @package gutesio\DataModelBundle\Classes
 */
class TagFormFieldGenerator
{
    public static function getFieldsForTag(string $technicalKey, array $types)
    {
        switch ($technicalKey) {
            case 'tag_delivery':
                return static::createFieldForDeliveryTag();
            case 'tag_wheelchair':
                return static::createFieldForWheelchairTag();
            case 'tag_corona':
                return static::createFieldForCoronaTag();
            case 'tag_familyFriendly':
                return static::createFieldForFamilyFriendlyTag();
            case 'tag_online_reservation':
                return static::createFieldForOnlineReservationTag($types);
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
            case 'tag_donation':
                return static::createFieldForDonationTag();
            default:
                return [];
        }
    }

    public static function getFieldsForTags(array $technicalKeys, array $types = [])
    {
        $fields = [];
        foreach ($technicalKeys as $technicalKey) {
            $fields = array_merge($fields, static::getFieldsForTag($technicalKey, $types));
        }

        return $fields;
    }

    public static function getNonMatchingFields(array $technicalKeys, array $types = [])
    {
        $allFields = static::getAllFields($types);
        $matchingFields = static::getFieldsForTags($technicalKeys,$types);
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

    public static function getAllFields($types = [])
    {
        $deliveryFields = static::createFieldForDeliveryTag();
        $wheelChairFields = static::createFieldForWheelchairTag();
        $coronaFields = static::createFieldForCoronaTag();
        $reservationFields = static::createFieldForOnlineReservationTag($types);
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

    private static function createFieldForfamilyFriendlyTag()
    {
        $fields = [];
        $field = new CKEditorFormField();
        $field->setName('familyFriendlyNotes');
        $field->setLabel($GLOBALS['TL_LANG']['form_tag_fields']['familyFriendlyNotes'][0]);
        $field->setDescription($GLOBALS['TL_LANG']['form_tag_fields']['familyFriendlyNotes'][1]);
        $field->setParagraphLabel($GLOBALS['TL_LANG']['form_tag_fields']['frontend']['paragraph']);
        $field->setHeadingLabel($GLOBALS['TL_LANG']['form_tag_fields']['frontend']['heading']);
        $fields[] = $field;

        return $fields;
    }

    private static function createFieldForOnlineReservationTag(array $types)
    {
        $fields = [];
        $field = new TextFormField();
        $field->setName('onlineReservationLink');
        $field->setLabel($GLOBALS['TL_LANG']['form_tag_fields']['onlineReservationLink'][0]);
        $field->setDescription($GLOBALS['TL_LANG']['form_tag_fields']['onlineReservationLink'][1]); //ToDo add booking to description
        $field->setValue("");
        $fields[] = $field;

        $reservationOptions = [];

        $settings = [];
        if ($types) {
            $typeIds = [];
            foreach ($types as $type) {
                if ($type['value'] && $type['label']) {
                    $typeIds[] = intval($type['value']);
                }
            }
            $objTypes = GutesioDataTypeModel::findMultipleByIds($typeIds);
            foreach ($objTypes as $objType) {
                $reservationSettings = unserialize($objType->reservationSettings);
                foreach ($reservationSettings as $reservationSetting) {
                    $settingsObj = C4gReservationSettingsModel::findById($reservationSetting);
                    if ($settingsObj) {
                        $settings[$reservationSetting] = $settingsObj->caption;
                    }
                }
            }
        }

        //ToDo load additional settings by showcase

        if (count($settings)) {
            //$reservationOptions[0] = ['value'=>0,'label'=>'Keine Reservierung über gutes.digital (Standard)'];
            foreach ($settings as $value=>$label) {
                $reservationOptions[] = [
                    'value' => $value,
                    'label' => $label,
                ];
            }

            $field = new SelectFormField();
            $field->setName('onlineReservationSettings'); // ToDo
            $field->setLabel('Vordefinierte Reservierungsformulare'); //ToDo Language
            $field->setDescription('Ein Standard wurde vordefiniert. Auf Wunsch kann die Auswahl ergänzt und individualisiert werden.'); //ToDo Language
            $field->setOptions($reservationOptions);
            $fields[] = $field;
        }

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

    private static function createFieldForTableReservationTag(array $types)
    {
        $fields = [];
        $field = new TextFormField();
        $field->setName('tableReservationLink');
        $field->setLabel($GLOBALS['TL_LANG']['form_tag_fields']['tableReservationLink'][0]);
        $field->setDescription($GLOBALS['TL_LANG']['form_tag_fields']['tableReservationLink'][1]);
        $fields[] = $field;

        $reservationOptions = [];

        $settings = [];
        if ($types) {
            $typeIds = [];
            foreach ($types as $type) {
                if ($type['value'] && $type['label']) {
                    $typeIds[] = intval($type['value']);
                }
            }
            $objTypes = GutesioDataTypeModel::findMultipleByIds($typeIds);
            foreach ($objTypes as $objType) {
                $reservationSettings = unserialize($objType->reservationSettings);
                foreach ($reservationSettings as $reservationSetting) {
                    $settingsObj = C4gReservationSettingsModel::findById($reservationSetting);
                    if ($settingsObj) {
                        $settings[$reservationSetting]  = $settingsObj->caption;
                    }
                }
            }
        }

        //ToDo load settings by showcase types
        foreach ($settings as $key=>$value) {
            $reservationOptions[] = [
                'value' => $key,
                'label' => $value,
            ];
        }

        if (count($reservationOptions)) {
            $field = new SelectFormField();
            $field->setName('onlineReservationSettings'); // ToDo
            $field->setLabel('Vordefinierte Reservierungsformulare'); //ToDo Language
            $field->setDescription('Ein Standard wurde vordefiniert. Auf Wunsch kann die Auswahl ergänzt und individualisiert werden.'); //ToDo Language
            $field->setOptions($reservationOptions);
            $fields[] = $field;
        }

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

    private static function createFieldForDonationTag()
    {
        $fields = [];
        $field = new TextFormField();
        $field->setName('donationLink');
        $field->setLabel($GLOBALS['TL_LANG']['form_tag_fields']['donationLink'][0]);
        $field->setDescription($GLOBALS['TL_LANG']['form_tag_fields']['donationLink'][1]);
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
