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
use con4gis\FrameworkBundle\Classes\FormFields\CKEditorFormField;
use con4gis\FrameworkBundle\Classes\FormFields\NumberFormField;
use con4gis\FrameworkBundle\Classes\FormFields\PDFUploadFormField;
use con4gis\FrameworkBundle\Classes\FormFields\SelectFormField;
use con4gis\FrameworkBundle\Classes\FormFields\TextAreaFormField;
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
        return match ($technicalKey) {
            'type_diet_cuisine' => static::getFieldsForDietCuisine(),
            'type_event_location' => static::getFieldsForEventLocation(),
            'type_admission' => static::getFieldsForAdmission(),
            'type_extra_zip' => static::getFieldsForExtraZip(),
            'type_surr_zip' => static::getFieldsForSurroundingZip(),
            'type_menu' => static::getFieldsForMenu(),
            'type_brochure_upload' => static::getFieldsForBrochureUpload(),
            'type_isbn' => static::getFieldsForIsbn(),
            'type_doctor_referral' => static::getFieldsForDoctorReferral(),
            'type_self_help_focus' => static::getFieldsForSelfHelpFocus(),
            'type_contact_info_advice_focus' => static::getFieldsForContactInfoAdviceFocus(),
            'type_administration' => static::getFieldsForAdministration(),
            'type_allergenes' => static::getFieldsForAllergenes(),
            'type_ingredients' => static::getFieldsForIngredients(),
            'type_food' => static::getFieldsForFood(),
            default => [],
        };
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
        $admissionFields = static::getFieldsForAdmission();
        $extraZipFields = static::getFieldsForExtraZip();
        $surrZipFields = static::getFieldsForSurroundingZip();
        $menuFields = static::getFieldsForMenu();
        $brochureUploadFields = static::getFieldsForBrochureUpload();
        $isbnFields = static::getFieldsForIsbn();

        return array_merge(
            $dietCuisineFields,
            $eventFields,
            $admissionFields,
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

        $field = new CKEditorFormField();
        $field->setName('technicalEquipment');
        $field->setLabel($GLOBALS['TL_LANG'][$strName]['technicalEquipment'] && (count($GLOBALS['TL_LANG'][$strName]['technicalEquipment']) > 0) ? $GLOBALS['TL_LANG'][$strName]['technicalEquipment'][0] : '');
        $field->setParagraphLabel($GLOBALS['TL_LANG'][$strName]['frontend']['paragraph'] ?: "Absatz");
        $field->setHeadingLabel($GLOBALS['TL_LANG'][$strName]['frontend']['heading'] ?: ['Titel', 'Untertitel']);
        $fields['technicalEquipment'] = $field;

        $field = new CheckboxFormField();
        $field->setName(self::FIELD_ALLOW_LOCATION_FOR_ALL);
        $field->setLabel($GLOBALS['TL_LANG'][$strName][self::FIELD_ALLOW_LOCATION_FOR_ALL] && (count($GLOBALS['TL_LANG'][$strName][self::FIELD_ALLOW_LOCATION_FOR_ALL]) > 0) ? $GLOBALS['TL_LANG'][$strName][self::FIELD_ALLOW_LOCATION_FOR_ALL][0] : '');
        $field->setDescription($GLOBALS['TL_LANG'][$strName][self::FIELD_ALLOW_LOCATION_FOR_ALL] && (count($GLOBALS['TL_LANG'][$strName][self::FIELD_ALLOW_LOCATION_FOR_ALL]) > 0) ? $GLOBALS['TL_LANG'][$strName][self::FIELD_ALLOW_LOCATION_FOR_ALL][1] : '');
        $field->setChecked(true);
        $fields[self::FIELD_ALLOW_LOCATION_FOR_ALL] = $field;

        return $fields;
    }

    private static function getFieldsForAdmission()
    {
        System::loadLanguageFile('form_tag_fields');
        $strName = 'form_tag_fields';

        $fields = [];

        $field = new CKEditorFormField();
        $field->setName('admissionPrices');
        $field->setLabel($GLOBALS['TL_LANG'][$strName]['admissionPrices'] && (count($GLOBALS['TL_LANG'][$strName]['admissionPrices']) > 0) ? $GLOBALS['TL_LANG'][$strName]['admissionPrices'][0] : '');
        $field->setParagraphLabel($GLOBALS['TL_LANG'][$strName]['frontend']['paragraph'] ?: "Absatz");
        $field->setHeadingLabel($GLOBALS['TL_LANG'][$strName]['frontend']['heading'] ?: ['Titel', 'Untertitel']);
        $fields['admissionPrices'] = $field;

        return $fields;
    }

    //ToDo auslagern
    private static function getFieldsForExtraZip()
    {
        System::loadLanguageFile('tl_gutesio_data_element');
        if (key_exists('tl_gutesio_data_element', $GLOBALS)) {
            $field = new TextFormField();
            $field->setName('extraZip');
            $field->setLabel($GLOBALS['TL_LANG']['tl_gutesio_data_element']['extraZip'] && (count($GLOBALS['TL_LANG']['tl_gutesio_data_element']['extraZip']) > 0) ? $GLOBALS['TL_LANG']['tl_gutesio_data_element']['extraZip'][0] : '');
            $field->setDescription($GLOBALS['TL_LANG']['tl_gutesio_data_element']['extraZip'] && (count($GLOBALS['TL_LANG']['tl_gutesio_data_element']['extraZip']) > 0) ? $GLOBALS['TL_LANG']['tl_gutesio_data_element']['extraZip'][1] : '');
            $field->setPattern('^[0-9]{5}(,[0-9]{5})*$');
            $field->setDynamicFieldlist(true);
            $field->setDynamicFieldlistUrl('/gutesio/maininstance/showcase/getTypeFields');
            $field->setDynamicFieldlistAdditionalFields([
                'types',
                'locationZip',
            ]);

            return ['extraZip' => $field];
        } else {
            return [];
        }
    }
    private static function getFieldsForSurroundingZip()
    {
        System::loadLanguageFile('tl_gutesio_data_element');
        if (key_exists('tl_gutesio_data_element', $GLOBALS)) {
            $field = new TextFormField();
            $field->setName('surrZip');
            $field->setLabel($GLOBALS['TL_LANG']['tl_gutesio_data_element']['surrZip'] && (count($GLOBALS['TL_LANG']['tl_gutesio_data_element']['surrZip']) > 0) ? $GLOBALS['TL_LANG']['tl_gutesio_data_element']['surrZip'][0] : '');
            $field->setDescription($GLOBALS['TL_LANG']['tl_gutesio_data_element']['surrZip'] && (count($GLOBALS['TL_LANG']['tl_gutesio_data_element']['surrZip']) > 0) ? $GLOBALS['TL_LANG']['tl_gutesio_data_element']['surrZip'][1] : '');
            $field->setPattern('^[0-9]{5}(,[0-9]{5})*$');

            return ['surrZip' => $field];
        } else {
            return [];
        }
    }

    //ToDo auslagern
    private static function getFieldsForIsbn()
    {
        $strName = 'main_instance_offer_form';
        $field = new TextFormField();
        $field->setName('isbn');
        $field->setLabel('ISBN');
        $field->setDescription('Geben Sie die ISBN für das Produkt ein.');

        return ['isbn' => $field];
    }

    //ToDo auslagern
    private static function getFieldsForDoctorReferral()
    {
        System::loadLanguageFile('main_instance_offer_form');
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

        asort($language['selfHelpFocusOptions'], SORT_STRING | SORT_FLAG_CASE );
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
        $field->setLabel('Selbsthilfethemen');
        $field->setDescription('Wählen Sie die passenden Einträge für Informationen zu Ihren Schwerpunkten aus.');
        $field->setEmptyOptionLabel('');
        $fields['selfHelpFocus'] = $field;

        $field = new TextFormField();
        $field->setName('turnDescription');
        $field->setLabel('Wann finden die Treffen statt?');
        $field->setDescription('Beschreiben Sie den Turnus der Treffen (z.B. jeden ersten Dienstag um 19 Uhr)');
        $fields['turnDescription'] = $field;

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

    private static function getFieldsForAllergenes()
    {
        $field = new TextAreaFormField();
        $field->setName('allergenes');
        $field->setLabel('Hinweise zu Allergenen');

        return ['allergenes' => $field];
    }

    private static function getFieldsForIngredients()
    {
        $field = new TextAreaFormField();
        $field->setName('ingredients');
        $field->setLabel('Zutaten');

        return ['ingredients' => $field];
    }

    private static function getFieldsForFood()
    {
        $fields = [];

        $field = new NumberFormField();
        $field->setName('kJ');
        $field->setLabel('Brennwert (in kJ)');
        $fields['kJ'] = $field;

        $field = new NumberFormField();
        $field->setName('fat');
        $field->setLabel('Fett (in g)');
        $fields['fat'] = $field;

        $field = new NumberFormField();
        $field->setName('saturatedFattyAcid');
        $field->setLabel('Davon gesättigte Fettsäuren (in g)');
        $fields['saturatedFattyAcid'] = $field;

        $field = new NumberFormField();
        $field->setName('carbonHydrates');
        $field->setLabel('Kohlenhydrate (in g)');
        $fields['carbonHydrates'] = $field;

        $field = new NumberFormField();
        $field->setName('sugar');
        $field->setLabel('Davon Zucker (in g)');
        $fields['sugar'] = $field;

        $field = new NumberFormField();
        $field->setName('salt');
        $field->setLabel('Salz (in g)');
        $fields['salt'] = $field;

        return $fields;
    }
}
