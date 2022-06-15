<?php
/**
 * This file belongs to gutes.digital and is published exclusively for use
 * in gutes.digital operator or provider pages.

 * @package    gutesio
 * @copyright  Küstenschmiede GmbH Software & Design (Matthias Eilers)
 * @link       https://gutes.digital
 */
namespace gutesio\DataModelBundle\Classes;

use con4gis\FrameworkBundle\Classes\FormFields\CheckboxFormField;
use con4gis\FrameworkBundle\Classes\FormFields\PDFUploadFormField;
use con4gis\FrameworkBundle\Classes\FormFields\SelectFormField;
use con4gis\FrameworkBundle\Classes\FormFields\TextFormField;
use con4gis\FrameworkBundle\Classes\Utility\RegularExpression;
use Contao\System;

/**
 * Class TypeFormFieldGenerator
 * Class for loading additional form fields for types by their technical keys.
 * @package gutesio\DataModelBundle\Classes
 */
class TypeFormFieldGenerator
{
    const FIELD_BROCHURE_UPLOAD = 'brochureUpload';
    const FIELD_MENU_LINK = 'menuLink';
    const FIELD_MENU_UPLOAD = 'menuUpload';
    const FIELD_ALLOW_LOCATION_FOR_ALL = 'allowLocationForAll';

    const FIELD_ISBN = 'isbn';

    public static function getFieldsForType(string $technicalKey)
    {
        switch ($technicalKey) {
            case 'type_diet_cuisine':
                return static::getFieldsForDietCuisine();
            case 'type_event_location':
                return static::getFieldsForEventLocation();
            case 'type_extra_zip':
                return static::getFieldsForExtraZip();
            case 'type_surr_zip':
                return static::getFieldsForSurroundingZip();
            case 'type_menu':
                return static::getFieldsForMenu();
            case 'type_brochure_upload':
                return static::getFieldsForBrochureUpload();
            case 'type_isbn':
                return static::getFieldsForIsbn();
            case 'type_doctor_referral':
                return static::getFieldsForDoctorReferral();
            case 'type_self_help_focus':
                return static::getFieldsForSelfHelpFocus();
            case 'type_contact_info_advice_focus':
                return static::getFieldsForContactInfoAdviceFocus();
            case 'type_administration':
                return static::getFieldsForAdministration();
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

        return $fields;
    }

    public static function getNonMatchingFields(array $technicalKeys)
    {
        $allFields = static::getAllFields();
        $matchingFields = static::getFieldsForTypes($technicalKeys);
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
        $dietCuisineFields = static::getFieldsForDietCuisine();
        $eventFields = static::getFieldsForEventLocation();
        $extraZipFields = static::getFieldsForExtraZip();
        $surrZipFields = static::getFieldsForSurroundingZip();
        $menuFields = static::getFieldsForMenu();
        $brochureUploadFields = static::getFieldsForBrochureUpload();
        $isbnFields = static::getFieldsForIsbn();

        return array_merge(
            $dietCuisineFields,
            $eventFields,
            $menuFields,
            $brochureUploadFields,
            $extraZipFields,
            $surrZipFields,
            $isbnFields
        );
    }

    private static function getFieldsForDietCuisine()
    {
        System::loadLanguageFile('field_translations');
        $language = $GLOBALS['TL_LANG']['gutesio'];
        $dietOptions = [];
        $cuisineOptions = [];
        foreach ($GLOBALS['gutesio']['diet_options'] as $entry) {
            $dietOptions[] = [
                'value' => $entry,
                'label' => $language['diet_options'][$entry],
            ];
        }
        foreach ($GLOBALS['gutesio']['cuisine_options'] as $entry) {
            $cuisineOptions[] = [
                'value' => $entry,
                'label' => $language['cuisine_options'][$entry],
            ];
        }

        $fields = [];
        $field = new SelectFormField();
        $field->setName('cuisine');
        $field->setMultiple(true);
        $field->setOptions($cuisineOptions);
        $field->setLabel('Küche');
        $field->setDescription('Wählen Sie die passenden Einträge für Informationen zu Ihrer Küche aus.');
        $field->setEmptyOptionLabel('');
        $fields['cuisine'] = $field;

        $field = new SelectFormField();
        $field->setName('diet');
        $field->setMultiple(true);
        $field->setOptions($dietOptions);
        $field->setLabel('Kost');
        $field->setDescription('Wählen Sie die passenden Einträge für Informationen zu Ihrer Kost aus.');
        $field->setEmptyOptionLabel('');
        $fields['diet'] = $field;

        return $fields;
    }

    private static function getFieldsForMenu()
    {
        $fields = [];

        $field = new TextFormField();
        $field->setName(self::FIELD_MENU_LINK);
        $field->setLabel('Speisekarte (Link)');
        $field->setPattern(RegularExpression::URL);
        $fields[self::FIELD_MENU_LINK] = $field;

        $field = new PDFUploadFormField();
        $field->setTitleFileTooBig('Datei zu groß');
        $field->setTextFileTooBig('Die von Ihnen hochgeladene Datei ist zu groß. Bitte wählen Sie eine andere aus.');
        $field->setName(self::FIELD_MENU_UPLOAD);
        $field->setLabel('Speisekarte (PDF Upload)');
        $field->setDescription('Hier können Sie Ihre Speisekarte als PDF-Datei hochladen.');
        $fields[self::FIELD_MENU_UPLOAD] = $field;

        return $fields;
    }

    private static function getFieldsForBrochureUpload()
    {
        $fields = [];
        $field = new PDFUploadFormField();
        $field->setTitleFileTooBig('Datei zu groß');
        $field->setTextFileTooBig('Die von Ihnen hochgeladene Datei ist zu groß. Bitte wählen Sie eine andere aus.');
        $field->setName(self::FIELD_BROCHURE_UPLOAD);
        $field->setLabel('Broschüre (PDF Upload)');
        $field->setDescription('Hier können Sie eine Broschüre als PDF-Datei hochladen.');
        $fields[self::FIELD_BROCHURE_UPLOAD] = $field;

        return $fields;
    }

    private static function getFieldsForEventLocation()
    {
        System::loadLanguageFile('form_tag_fields');
        $strName = 'form_tag_fields';

        $fields = [];

        $field = new TextFormField();
        $field->setName('maxPersons');
        $field->setLabel($GLOBALS['TL_LANG'][$strName]['maxPersons'] && (count($GLOBALS['TL_LANG'][$strName]['maxPersons']) > 0) ? $GLOBALS['TL_LANG'][$strName]['maxPersons'][0] : '');
        $fields['maxPersons'] = $field;

        $field = new TextFormField();
        $field->setName('technicalEquipment');
        $field->setLabel($GLOBALS['TL_LANG'][$strName]['technicalEquipment'] && (count($GLOBALS['TL_LANG'][$strName]['technicalEquipment']) > 0) ? $GLOBALS['TL_LANG'][$strName]['technicalEquipment'][0] : '');
        $fields['technicalEquipment'] = $field;

        $field = new CheckboxFormField();
        $field->setName(self::FIELD_ALLOW_LOCATION_FOR_ALL);
        $field->setLabel($GLOBALS['TL_LANG'][$strName][self::FIELD_ALLOW_LOCATION_FOR_ALL] && (count($GLOBALS['TL_LANG'][$strName][self::FIELD_ALLOW_LOCATION_FOR_ALL]) > 0) ? $GLOBALS['TL_LANG'][$strName][self::FIELD_ALLOW_LOCATION_FOR_ALL][0] : '');
        $field->setDescription($GLOBALS['TL_LANG'][$strName][self::FIELD_ALLOW_LOCATION_FOR_ALL] && (count($GLOBALS['TL_LANG'][$strName][self::FIELD_ALLOW_LOCATION_FOR_ALL]) > 0) ? $GLOBALS['TL_LANG'][$strName][self::FIELD_ALLOW_LOCATION_FOR_ALL][1] : '');
        $field->setChecked(true);
        $fields[self::FIELD_ALLOW_LOCATION_FOR_ALL] = $field;

        return $fields;
    }

    private static function getFieldsForExtraZip()
    {
        $field = new TextFormField();
        $field->setName('extraZip');
        $field->setLabel($GLOBALS['TL_LANG']['tl_gutesio_data_element']['extraZip']     && (count($GLOBALS['TL_LANG']['tl_gutesio_data_element']['extraZip']) > 0) ? $GLOBALS['TL_LANG']['tl_gutesio_data_element']['extraZip'][0] : '');
        $field->setDescription($GLOBALS['TL_LANG']['tl_gutesio_data_element']['extraZip'] && (count($GLOBALS['TL_LANG']['tl_gutesio_data_element']['extraZip']) > 0) ? $GLOBALS['TL_LANG']['tl_gutesio_data_element']['extraZip'][1] : '');
        $field->setPattern('^[0-9]{5}(,[0-9]{5})*$');
        $field->setDynamicFieldlist(true);
        $field->setDynamicFieldlistUrl('/gutesio/maininstance/showcase/getTypeFields');
        $field->setDynamicFieldlistAdditionalFields([
            'types',
            'locationZip',
        ]);

        return ['extraZip' => $field];
    }
    private static function getFieldsForSurroundingZip()
    {
        $field = new TextFormField();
        $field->setName('surrZip');
        $field->setLabel($GLOBALS['TL_LANG']['tl_gutesio_data_element']['surrZip']     && (count($GLOBALS['TL_LANG']['tl_gutesio_data_element']['surrZip']) > 0) ? $GLOBALS['TL_LANG']['tl_gutesio_data_element']['surrZip'][0] : '');
        $field->setDescription($GLOBALS['TL_LANG']['tl_gutesio_data_element']['surrZip'] && (count($GLOBALS['TL_LANG']['tl_gutesio_data_element']['surrZip']) > 0) ? $GLOBALS['TL_LANG']['tl_gutesio_data_element']['surrZip'][1] : '');
        $field->setPattern('^[0-9]{5}(,[0-9]{5})*$');

        return ['surrZip' => $field];
    }

    private static function getFieldsForIsbn()
    {
        $strName = 'main_instance_offer_form';
        $field = new TextFormField();
        $field->setName('isbn');
        $field->setLabel('ISBN');
        $field->setDescription('Geben Sie die ISBN für das Produkt ein.');

        return ['isbn' => $field];
    }

    private static function getFieldsForDoctorReferral()
    {
        $strName = 'main_instance_offer_form';
        $fields = [];

        $field = new CheckboxFormField();
        $field->setName('doctorReferral');
        $field->setLabel($GLOBALS['TL_LANG'][$strName]['doctorReferral'] && (count($GLOBALS['TL_LANG'][$strName]['doctorReferral']) > 0) ? $GLOBALS['TL_LANG'][$strName]['doctorReferral'][0] : '');
        $field->setDescription($GLOBALS['TL_LANG'][$strName]['doctorReferral'] && (count($GLOBALS['TL_LANG'][$strName]['doctorReferral']) > 0) ? $GLOBALS['TL_LANG'][$strName]['doctorReferral'][1] : '');
        $field->setChecked(true);
        $fields['doctorReferral'] = $field;

        return $fields;
    }

    private static function getFieldsForSelfHelpFocus()
    {
        $fields = [];
        $options = [];

        System::loadLanguageFile('field_translations', 'de');
        $language = $GLOBALS['TL_LANG']['gutesio'];

        foreach ($language['selfHelpFocusOptions'] as $key => $entry) {
            $options[] = [
                'value' => $key,
                'label' => $entry,
            ];
        }

        $field = new SelectFormField();
        $field->setMultiple(true);
        $field->setName('selfHelpFocus');
        $field->setOptions($options);
        $field->setLabel('Schwerpunkte');
        $field->setDescription('Wählen Sie die passenden Einträge für Informationen zu Ihren Schwerpunkten aus.');
        $field->setEmptyOptionLabel('');
        $fields['selfHelpFocus'] = $field;

        return $fields;
    }

    private static function getFieldsForContactInfoAdviceFocus()
    {
        System::loadLanguageFile('field_translations', 'de');
        $language = $GLOBALS['TL_LANG']['gutesio'];

        $fields = [];
        $options = [];

        foreach ($language['contactInfoAdviceFocusOptions'] as $key => $entry) {
            $options[] = [
                'value' => $key,
                'label' => $entry,
            ];
        }

        $field = new SelectFormField();
        $field->setMultiple(true);
        $field->setName('contactInfoAdviceFocus');
        $field->setOptions($options);
        $field->setLabel('Schwerpunkte');
        $field->setDescription('Wählen Sie die passenden Einträge für Informationen zu Ihren Schwerpunkten aus.');
        $field->setEmptyOptionLabel('');
        $fields['contactInfoAdviceFocus'] = $field;

        return $fields;
    }

    private static function getFieldsForAdministration()
    {
        $field = new TextFormField();
        $field->setName('administration');
        $field->setLabel('Leitende Person');
        $field->setDescription('Geben Sie den Namen der leitenden Person an.');

        return ['administration' => $field];
    }
}
