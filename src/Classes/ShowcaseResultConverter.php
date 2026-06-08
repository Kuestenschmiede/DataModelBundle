<?php
/**
 * This file belongs to gutes.digital and is published exclusively for use
 * in gutes.digital operator or provider pages.

 * @package    gutesio
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design (Matthias Eilers)
 * @link       https://gutes.digital
 */
namespace gutesio\DataModelBundle\Classes;

use con4gis\CoreBundle\Classes\C4GUtils;
use con4gis\CoreBundle\Resources\contao\models\C4gLogModel;
use Contao\Controller;
use Contao\Database;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\System;
use gutesio\OperatorBundle\Classes\Models\GutesioOperatorSettingsModel;

/**
 * Class ShowcaseResultConverter
 * @package gutesio\DataModelBundle\Classes
 */
class ShowcaseResultConverter
{
    private static $cachedTypes = [];

    private static $processedTags = [];

    private $fileUploadFields = [
        TypeFormFieldGenerator::FIELD_BROCHURE_UPLOAD,
        TypeFormFieldGenerator::FIELD_MENU_UPLOAD,
    ];

    /**
     * Converts the data into the format needed by the client.
     * @param $arrResult
     * @param array $arrOptions
     * @return array
     */
    public function convertDbResult($arrResult, $arrOptions = [], $fileUtils = new FileUtils()) : array
    {
        $db = Database::getInstance();
        $checker = new ImprintConstraintChecker();
        System::loadLanguageFile('field_translations','de');
        $objSettings = GutesioOperatorSettingsModel::findSettings();
        $cdnUrl = $objSettings ? $objSettings->cdnUrl : 'https://cdn.con4gis.cloud';
        if (!$cdnUrl) {
            $cdnUrl = 'https://cdn.con4gis.cloud';
        }
        $cdnUrl = rtrim($cdnUrl, '/');
        // C4gLogModel::addLogEntry('data-model', "ShowcaseResultConverter: Using CDN URL: " . $cdnUrl);

        // setup
        $this->loadTypes();
        $this->loadTags();

        $data = [];
        if (count($arrResult) === 0) {
            // no data
            return [];
        }
        foreach ($arrResult as $result) {
            $datum = [];

            if (!is_array($result)) {
                continue;
            }
            // for wishlist
            if (key_exists('internal_type',$result)) {
                $datum['internal_type'] = $result['internal_type'];
            }
            $datum['name'] = html_entity_decode($result['name']);
            //hotfix special char
            $datum['name'] = str_replace('&#39;', "'", $datum["name"]);

            $datum['id'] = $result['id'];
            $datum['uuid'] = $result['uuid'];
            $datum['ownerGroupId'] = $result['ownerGroupId'];
            $datum['ownerMemberId'] = $result['ownerMemberId'];
            $datum['description'] = html_entity_decode(C4GUtils::replaceInsertTags($result['description']));
            $datum['directions'] = html_entity_decode($result['directions']);
            $datum['surroundings'] = html_entity_decode($result['surroundings']);
            $datum['internalDescription'] = key_exists('internalDescription', $result) ? html_entity_decode($result['internalDescription']) : '';
            $datum['imageCredits'] = html_entity_decode($result['imageCredits']);
            $datum['importantNotes'] = html_entity_decode($result['importantNotes']);
            $datum['safetyInstructions'] = key_exists('safetyInstructions', $result) ? html_entity_decode($result['safetyInstructions']) : '';
            $datum['tips'] = key_exists('tips', $result) ? html_entity_decode($result['tips']) : '';
            $datum['additionalInformation'] = key_exists('additionalInformation', $result) ? html_entity_decode($result['additionalInformation']) : '';
            $datum['technicalEquipment'] = key_exists('technicalEquipment', $result) ? html_entity_decode($result['technicalEquipment']) : '';
            $datum['equipment'] = key_exists('equipment', $result) ? html_entity_decode($result['equipment']) : '';
            $datum['admissionPrices'] = key_exists('admissionPrices', $result) ? html_entity_decode($result['admissionPrices']) : '';
            $datum['alias'] = $result['alias'];
            $datum['geox'] = (!empty($result['geox']) && $result['geox'] != 0) ? $result['geox'] : '';
            $datum['geoy'] = (!empty($result['geoy']) && $result['geoy'] != 0) ? $result['geoy'] : '';
            $datum['geojson'] = key_exists('geojson',$result) ? html_entity_decode($result['geojson']) : '';
            $datum['email'] = html_entity_decode($result['email']);
            $datum['phone'] = html_entity_decode($result['phone']);
            $datum['mobile'] = html_entity_decode($result['mobile']);
            $datum['fax'] = html_entity_decode($result['fax']);
            $datum['contactEmail'] = html_entity_decode($result['contactEmail']);
            $datum['contactPhone'] = html_entity_decode($result['contactPhone']);
            $datum['contactMobile'] = html_entity_decode($result['contactMobile']);
            $datum['contactFax'] = html_entity_decode($result['contactFax']);
            $datum['website'] = C4GUtils::addProtocolToLink($result['website']);
            $datum['facebook'] = C4GUtils::addProtocolToLink($result['facebook']);
            //$datum['twitter'] = C4GUtils::addProtocolToLink($result['twitter']);
            $datum['instagram'] = C4GUtils::addProtocolToLink($result['instagram']);
            $datum['xing'] = C4GUtils::addProtocolToLink($result['xing']);
            $datum['linkedin'] = C4GUtils::addProtocolToLink($result['linkedin']);
            $datum['whatsapp'] = html_entity_decode($result['whatsapp']);
            if (strpos($datum['whatsapp'], 'https') === false) {
                // not a link, but a number
                // check if first digit is a 0, that must be stripped out
                if (strpos($datum['whatsapp'], '0') === 0) {
                    $datum['whatsapp'] = substr($datum['whatsapp'], 1);
                    $datum['whatsapp'] = str_replace(' ', '', $datum['whatsapp']);
                    $datum['whatsapp'] = $datum['whatsapp'] ? 'https://wa.me/+49' . $datum['whatsapp'] : $datum['whatsapp'];
                } else if (strpos($datum['whatsapp'], '+') === 0) {
                    $datum['whatsapp'] = str_replace(' ', '', $datum['whatsapp']);
                    $datum['whatsapp'] = $datum['whatsapp'] ? 'https://wa.me/' . $datum['whatsapp'] : $datum['whatsapp'];
                }
            }
            $datum['published'] = intval($result['published']);
            $datum['allowLogoDisplay'] = intval($result['allowLogoDisplay']);
            $datum['distance'] = key_exists('distance',$result) ? $result['distance'] : '';
            $datum['videoPreview'] = [
                'videoType' => $result['videoType'],
                'video' => html_entity_decode($result['videoLink']),
            ];
            if (key_exists('videoPreviewImageCDN', $result) && $result['videoPreviewImageCDN']) {
                $uuid = $result['videoPreviewImage'] ?? '';
                if ($uuid) {
                    $uuid = StringUtil::deserialize($uuid);
                    if (C4GUtils::isBinary($uuid)) { $uuid = StringUtil::binToUuid($uuid); }
                }
                $datum['videoPreview']['videoPreviewImage'] = $this->createFileDataFromFile($result['videoPreviewImageCDN'], false, $fileUtils, 600, 450, $datum['name'], $datum['name'], $uuid ?: '');
                $datum['videoPreviewImage'] = $datum['videoPreview']['videoPreviewImage'];
            }
            $datum['youtubeChannelLink'] = C4GUtils::addProtocolToLink($result['youtubeChannelLink']);
            $datum['vimeoChannelLink'] = C4GUtils::addProtocolToLink($result['vimeoChannelLink']);
            $datum['wikipediaLink'] = C4GUtils::addProtocolToLink($result['wikipediaLink']);
            $datum['androidAppLink'] = C4GUtils::addProtocolToLink($result['androidAppLink']);
            $datum['iosAppLink'] = C4GUtils::addProtocolToLink($result['iosAppLink']);
            $datum['deviatingPhoneHours'] = $result['deviatingPhoneHours'];
            $datum['phoneHours'] = html_entity_decode($result['phoneHours']);
            if ($result['opening_hours'] && strpos($result['opening_hours'], '"') === 0) {
                $datum['opening_hours'] = html_entity_decode(str_replace(array("\r\n", "\r", "\n"), '', $result['opening_hours']));
            } else {
                $datum['opening_hours'] = html_entity_decode($result['opening_hours']);
            }
            $datum['opening_hours_additional'] = html_entity_decode($result['opening_hours_additional']);
            $datum['legalTextSet'] = intval($result['legalTextSet']);
            $datum['allowedPaymentMethods'] = StringUtil::deserialize($result['allowedPaymentMethods'], true);
            $datum['cashOnlyIfPickup'] = $result['cashOnlyIfPickup'];
            $datum['displayRequest'] = key_exists('displayRequest', $result) ? $result['displayRequest'] : '';
            $datum['displaySlogan'] = $result['displaySlogan'];
            $datum['operators'] = [];
            $datum['source'] = $result['source'];
            $datum['foreignKey'] = isset($result['foreignKey']) && strlen($result['foreignKey']) > 2 ? '1' : '';


            if (key_exists('operators',$result)) {
                foreach ($result['operators'] as $operator) {
                    $datum['operators'][] = [
                        'value' => $operator['operatorId'],
                        'label' => html_entity_decode($operator['name']),
                    ];
                }
            }

            // load types
            $datum['types'] = [];
            $resultTypeIds = $db
                ->prepare('SELECT typeId FROM tl_gutesio_data_element_type WHERE elementId = ? ORDER BY `rank` ASC')
                ->execute($result['uuid'])
                ->fetchAllAssoc();

            $arrTypeIds = array_column($resultTypeIds, 'typeId');

            foreach ($arrTypeIds as $typeId) {
                if (key_exists($typeId, static::$cachedTypes)) {
                    $datum['types'][$typeId] = static::$cachedTypes[$typeId];
                }
            }

            //fix wrong json encoding as object
            $types = $datum['types'];
            $datum['types'] = [];
            foreach  ($types as $key=>$value) {
                $datum['types'][] = $value;
            }

            $dietLabels = $GLOBALS['TL_LANG']['gutesio']['diet_options'];
            $cuisineLabels = $GLOBALS['TL_LANG']['gutesio']['cuisine_options'];
            $otherLabels = $GLOBALS['TL_LANG']['gutesio'];
            $otherFields = [
                'selfHelpFocus',
                'contactInfoAdviceFocus',
            ];
            // load type values
            $sql = 'SELECT `typeFieldKey`, `typeFieldValue`, `typeFieldFile`, `typeFieldFileCDN` FROM tl_gutesio_data_type_element_values WHERE elementId = ?';
            $typeElementValues = $db->prepare($sql)->execute($datum['uuid'])->fetchAllAssoc();
            foreach ($typeElementValues as $typeElementValue) {
                // check if the value is serialized
                $fieldKey = $typeElementValue['typeFieldKey'];
                $fieldValue = StringUtil::deserialize($typeElementValue['typeFieldValue']);
                if (is_array($fieldValue) && key_exists('details', $arrOptions) && $arrOptions['details']) {
                    $resultValue = '';

                    foreach ($fieldValue as $key => $value) {
                        if (is_array($value)) { //possible?
                            $resultValue .= $value['label'];
                        } else {
                            if ($fieldKey === 'cuisine') {
                                $resultValue .= $cuisineLabels[$value];
                            } elseif ($fieldKey === 'diet') {
                                $resultValue .= $dietLabels[$value];
                            } elseif (in_array($fieldKey, $otherFields)) {
                                $resultValue .= $otherLabels[$fieldKey . 'Options'][$value];
                            } else {
                                $resultValue .= $value;
                            }
                        }
                        if ($key !== array_key_last($fieldValue)) {
                            $resultValue .= ', ';
                        }
                    }
                    $datum[$fieldKey] = $resultValue;
                } elseif (in_array($fieldKey, $this->fileUploadFields)) {
                    $uuid = $typeElementValue['typeFieldFile'];
                    if ($uuid) {
                        if (C4GUtils::isBinary($uuid)) {
                            $uuid = StringUtil::binToUuid($uuid);
                        }
                    } else {
                        $uuid = '';
                    }

                    if ($arrOptions && key_exists('withoutCDN', $arrOptions) && $arrOptions['withoutCDN'] && $typeElementValue['typeFieldFile']) {
                        $fileModel = FilesModel::findByUuid($uuid);
                        if ($fileModel) {
                            $datum[$fieldKey] = [
                                'data' => [],
                                'name' => $fileModel->name,
                                'changed' => false,
                                'path' => $fileModel->path,
                                'uuid' => $uuid
                            ];
                        }
                    } else if ($typeElementValue['typeFieldFileCDN']) {
                        $datum[$fieldKey] = [
                            'data' => [],
                            'name' => $fieldKey,
                            'changed' => false,
                            'path' => $fileUtils->addUrlToPathAndGetImage($cdnUrl, $typeElementValue['typeFieldFileCDN']),
                            'uuid' => $uuid
                        ];
                    }
                } else {
                    $datum[$fieldKey] = $fieldValue;
                }
            }

            // load tags
            $datum['tags'] = [];
            $arrTagIds = $db
                ->prepare('SELECT tagId FROM tl_gutesio_data_tag_element WHERE elementId = ?')
                ->execute($result['uuid'])->fetchEach('tagId');

            foreach ($arrTagIds as $tagId) {
                if (!isset(static::$processedTags[$tagId])) {
                    continue;
                }
                $tag = static::$processedTags[$tagId];

                switch ($tag['technicalKey']) {
                    case 'tag_delivery':
                        $stmt = $db->prepare(
                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                        $tagResult = $stmt->execute(
                            $datum['uuid'],
                            'deliveryServiceLink'
                        )->fetchAssoc();

                        if ($tagResult) {
                            $tagLink = $tagResult['tagFieldValue'];
                            $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                            $tag['linkLabel'] = 'Lieferservice';
                        }

                        break;
                    case 'tag_online_reservation':
                        $stmt = $db->prepare(
                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                        $tagResult = $stmt->execute(
                            $datum['uuid'],
                            'onlineReservationLink'
                        )->fetchAssoc();

                        if ($tagResult) {
                            $tagLink = $tagResult['tagFieldValue'];
                            if (strpos($tagLink, '@') !== false) {
                                if (strpos($tagLink, 'mailto:') !== 0) {
                                    $tag['linkHref'] = 'mailto:' . $tagLink;
                                }
                            } else {
                                $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                            }
                            $tag['linkLabel'] = 'Onlinereservierung';
                        }

                        break;
                    case 'tag_clicknmeet':
                        $stmt = $db->prepare(
                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                        $tagResult = $stmt->execute(
                            $datum['uuid'],
                            'clicknmeetLink'
                        )->fetchAssoc();
                        if ($tagResult) {
                            $tagLink = $tagResult['tagFieldValue'];
                            $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                            $tag['linkLabel'] = 'Click & Meet';
                        }

                        break;
                    case 'tag_table_reservation':
                        $stmt = $db->prepare(
                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                        $tagResult = $stmt->execute(
                            $datum['uuid'],
                            'tableReservationLink'
                        )->fetchAssoc();

                        if ($tagResult) {
                            $tagLink = $tagResult['tagFieldValue'];
                            $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                            $tag['linkLabel'] = 'Tischreservierung';
                        }

                        break;
                    case 'tag_onlineshop':
                        $stmt = $db->prepare(
                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                        $tagResult = $stmt->execute(
                            $datum['uuid'],
                            'onlineShopLink'
                        )->fetchAssoc();

                        if ($tagResult) {
                            $tagLink = $tagResult['tagFieldValue'];
                            $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                            $tag['linkLabel'] = 'Onlineshop';
                        }

                        break;
                    case 'tag_help_support':
                        $stmt = $db->prepare(
                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                        $tagResult = $stmt->execute(
                            $datum['uuid'],
                            'helpSupport'
                        )->fetchAssoc();

                        if ($tagResult) {
                            $tagLink = $tagResult['tagFieldValue'];
                            $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                            $tag['linkLabel'] = 'Hilfe / Support';
                        }

                        break;
                    case 'tag_discussion_forum':
                        $stmt = $db->prepare(
                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                        $tagResult = $stmt->execute(
                            $datum['uuid'],
                            'discussionForum'
                        )->fetchAssoc();

                        if ($tagResult) {
                            $tagLink = $tagResult['tagFieldValue'];
                            $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                            $tag['linkLabel'] = 'Diskussionsforum';
                        }

                        break;
                    case 'tag_ios_app':
                        $stmt = $db->prepare(
                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                        $tagResult = $stmt->execute(
                            $datum['uuid'],
                            'iosApp'
                        )->fetchAssoc();

                        if ($tagResult) {
                            $tagLink = $tagResult['tagFieldValue'];
                            $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                            $tag['linkLabel'] = 'iOS-App';
                        }

                        break;
                    case 'tag_android_app':
                        $stmt = $db->prepare(
                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                        $tagResult = $stmt->execute(
                            $datum['uuid'],
                            'androidApp'
                        )->fetchAssoc();

                        if ($tagResult) {
                            $tagLink = $tagResult['tagFieldValue'];
                            $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                            $tag['linkLabel'] = 'Android-App';
                        }

                        break;
                    case 'tag_online_counseling':
                        $stmt = $db->prepare(
                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                        $tagResult = $stmt->execute(
                            $datum['uuid'],
                            'onlineCounseling'
                        )->fetchAssoc();

                        if ($tagResult) {
                            $tagLink = $tagResult['tagFieldValue'];
                            $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                            $tag['linkLabel'] = 'Online-Beratung';
                        }

                        break;
                    case 'tag_online_chat':
                        $stmt = $db->prepare(
                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                        $tagResult = $stmt->execute(
                            $datum['uuid'],
                            'onlineChat'
                        )->fetchAssoc();

                        if ($tagResult) {
                            $tagLink = $tagResult['tagFieldValue'];
                            $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                            $tag['linkLabel'] = 'Online-Chat';
                        }

                        break;
                    case 'tag_online_video_forum':
                        $stmt = $db->prepare(
                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                        $tagResult = $stmt->execute(
                            $datum['uuid'],
                            'onlineVideoForum'
                        )->fetchAssoc();

                        if ($tagResult) {
                            $tagLink = $tagResult['tagFieldValue'];
                            $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                            $tag['linkLabel'] = 'Online-Videoforum';
                        }

                        break;
                    case 'tag_online_therapy_program':
                        $stmt = $db->prepare(
                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                        $tagResult = $stmt->execute(
                            $datum['uuid'],
                            'onlineTherapyProgram'
                        )->fetchAssoc();

                        if ($tagResult) {
                            $tagLink = $tagResult['tagFieldValue'];
                            $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                            $tag['linkLabel'] = 'Online-Therapieprogramm';
                        }

                        break;
                    case 'tag_tariff_calculator':
                        $stmt = $db->prepare(
                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                        $tagResult = $stmt->execute(
                            $datum['uuid'],
                            'tariffCalculator'
                        )->fetchAssoc();

                        if ($tagResult) {
                            $tagLink = $tagResult['tagFieldValue'];
                            $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                            $tag['linkLabel'] = 'Tarifrechner';
                        }

                        break;
                    case 'tag_donation':
                        $stmt = $db->prepare(
                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                        $tagResult = $stmt->execute(
                            $datum['uuid'],
                            'donationLink'
                        )->fetchAssoc();

                        if ($tagResult) {
                            $tagLink = $tagResult['tagFieldValue'];
                            $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                            $tag['linkLabel'] = 'Spendenlink';
                        }

                        break;
                    default:
                        break;
                }

                if ($tag) {
                    $datum['tags'][] = $tag;
                }
            }

            // hotfix labels for frontend select fields
            foreach ($datum['tags'] as $k => $tag) {
                if (is_array($tag) && !isset($tag['label']) && isset($tag['name'])) {
                    $datum['tags'][$k]['label'] = html_entity_decode($tag['name']);
                }
            }

            $checkTagList = [];
            foreach($datum['tags'] as $tag) {
                if ($tag['value']) {
                    $checkTagList[$tag['value']] = $tag;
                } else {
                    $checkTagList[] = $tag;
                }
            }

            $datum['tags'] = [];
            foreach ($checkTagList as $tag) {
                $datum['tags'][] = $tag;
            }

            // load tag values
            $sql = 'SELECT `tagFieldKey`, `tagFieldValue` FROM tl_gutesio_data_tag_element_values WHERE elementId = ?';
            $tagElementValues = $db->prepare($sql)->execute($datum['uuid'])->fetchAllAssoc();
            foreach ($tagElementValues as $tagElementValue) {
                // avoid overriding type values with same key
                if (!key_exists($tagElementValue['tagFieldKey'], $datum) || !$datum[$tagElementValue['tagFieldKey']]) {
                    if ($tagElementValue['tagFieldKey'] === 'onlineReservationLink') {
                        if (strpos($tagElementValue['tagFieldValue'], '@') !== false) {
                            if (strpos($tagElementValue['tagFieldValue'], 'mailto:') !== 0) {
                                $tagElementValue['tagFieldValue'] = 'mailto:' . html_entity_decode($tagElementValue['tagFieldValue']);
                            }
                        }
                    }
                    $datum[$tagElementValue['tagFieldKey']] = html_entity_decode($tagElementValue['tagFieldValue']);
                }
            }

            // load related showcases
            $showcaseIds = StringUtil::deserialize($result['showcaseIds']);
            if ($showcaseIds && (count($showcaseIds) > 0 || ($showcaseIds !== ''))) {
                $idString = '(' ;
                foreach ($showcaseIds as $key => $showcaseId) {
                    $idString .= "\"$showcaseId\"";
                    if (!(array_key_last($showcaseIds) === $key)) {
                        $idString .= ',';
                    }
                }
                $idString .= ')';
                if ($idString !== '()') {
                    $showcases = $db->prepare("SELECT * FROM tl_gutesio_data_element WHERE `uuid` IN $idString")
                        ->execute()->fetchAllAssoc();
                    $idx = 0;
                    // check for relation on both sides
                    // if showcases are related on both sides, show the logo of the related showcase
                    $processedIds = [];
                    foreach ($showcases as $showcase) {
                        $relatedIds = StringUtil::deserialize($showcase['showcaseIds']);
                        if ($relatedIds) {
                            foreach ($relatedIds as $relatedId) {
                                if ($relatedId === $datum['uuid']) {
                                    if ($showcase['allowLogoDisplay'] && $showcase['logoCDN'] && !in_array($showcase['uuid'], $processedIds)) {
                                        //$logoModel = FilesModel::findByUuid(StringUtil::binToUuid($showcase['logo']));
                                        if ($showcase['logoCDN']) {
                                            if (!key_exists('relatedShowcaseLogos', $datum) || !$datum['relatedShowcaseLogos']) {
                                                $datum['relatedShowcaseLogos'] = [];
                                            }
                                            // this array is needed to restrict the options for the type filter correctly
                                            if (!key_exists('relatedShowcases', $datum) || !$datum['relatedShowcases']) {
                                                $datum['relatedShowcases'] = [];
                                            }
                                            //$logoData = $this->createFileDataFromModel($logoModel);
                                            $logoData = $this->createFileDataFromFile($showcase['logoCDN'], false, $fileUtils, 0, 150, $showcase['name'], $showcase['name']);;
                                            $logoData['href'] = $showcase['alias'];
                                            $datum['relatedShowcaseLogos'][] = $logoData;
                                            $datum['relatedShowcases'][] = [
                                                'uuid' => $showcase['uuid'],
                                                'foreignLink' => $showcase['foreignLink'],
                                                'releaseType' => $showcase['releaseType'],
                                                'name' => html_entity_decode($showcase['name']),
                                            ];
                                            $idx++;
                                        }
                                        $processedIds[] = $showcase['uuid'];
                                    }
                                }
                            }
                        }
                    }

                    $datum['showcaseIds'] = $this->convertToOptions($showcases);
                } else {
                    $datum['showcaseIds'] = [];
                }
            } else {
                $datum['showcaseIds'] = [];
            }

            $datum['locationName'] = $result['name'];
            $datum['locationCity'] = $result['locationCity'];
            $datum['locationZip'] = $result['locationZip'];
            $datum['locationStreet'] = $result['locationStreet'];
            $datum['locationStreetNumber'] = $result['locationStreetNumber'];
            $datum['locationStreetSuffix'] = $result['locationStreetSuffix'];

            $datum['contactable'] = $result['contactable'];
            $datum['contactName'] = html_entity_decode($result['contactName']);
            $datum['contactAdditionalName'] = html_entity_decode($result['contactAdditionalName']);
            if (!$datum['contactName'] && !$datum['contactAdditionalName']) {
                $datum['contactName'] = $datum['name'];
            }
            $datum['contactZip'] = $result['contactZip'];
            $datum['contactCity'] = $result['contactCity'];
            $datum['contactStreet'] = $result['contactStreet'];
            $datum['contactStreetNumber'] = $result['contactStreetNumber'];
            if (key_exists('directory', $result) && $result['directory']) {
                $datum['directory'] = $result['directory'];
            }

            // --- Consolidated Image Processing ---
            $useCDN = !($arrOptions['withoutCDN'] ?? false);
            
            // 1. Process Header Image
            $datum['image'] = null;
            $uuidVal = StringUtil::deserialize($result['image']);
            $uuidStr = $uuidVal ? (C4GUtils::isBinary($uuidVal) ? StringUtil::binToUuid($uuidVal) : $uuidVal) : '';
            
            $model = null;
            if ($uuidVal) {
                $model = FilesModel::findByUuid($uuidVal);
                if ($model === null && !C4GUtils::isBinary($uuidVal)) {
                    $model = FilesModel::findByUuid(StringUtil::uuidToBin($uuidVal));
                }
            }

            if ($result['imageCDN'] && ($useCDN || !$model)) {
                $datum['image'] = $this->createFileDataFromFile($result['imageCDN'], false, $fileUtils, 600, 450, $result['name'], $result['name'], $uuidStr, $arrOptions['directCDN'] ?? false);
            } elseif ($model) {
                $datum['image'] = $this->createFileDataFromModel($model, false, $fileUtils);
            } else {
                $fallbackPath = 'files/showcases/' . $uuidStr . '/';
                if ($uuidStr) {
                    $rootDir = System::getContainer()->getParameter('kernel.project_dir');
                    if (is_dir($rootDir . '/' . $fallbackPath)) {
                        $files = scandir($rootDir . '/' . $fallbackPath);
                        foreach ($files as $f) {
                            if ($f !== '.' && $f !== '..' && is_file($rootDir . '/' . $fallbackPath . $f)) {
                                $datum['image'] = $this->createFileDataFromFile($fallbackPath . $f, false, $fileUtils, 600, 450, $result['name'], $result['name'], $uuidStr, $arrOptions['directCDN'] ?? false);
                                if ($datum['image']) break;
                            }
                        }
                    }
                }
            }

            // 2. Process Logo
            $datum['logo'] = null;
            $uuidVal = StringUtil::deserialize($result['logo']);
            $uuidStr = $uuidVal ? (C4GUtils::isBinary($uuidVal) ? StringUtil::binToUuid($uuidVal) : $uuidVal) : '';
            
            $model = null;
            if ($uuidVal) {
                $model = FilesModel::findByUuid($uuidVal);
                if ($model === null && !C4GUtils::isBinary($uuidVal)) {
                    $model = FilesModel::findByUuid(StringUtil::uuidToBin($uuidVal));
                }
            }

            if ($result['logoCDN'] && ($useCDN || !$model)) {
                $datum['logo'] = $this->createFileDataFromFile($result['logoCDN'], false, $fileUtils, 0, 150, $result['name'], $result['name'], $uuidStr, $arrOptions['directCDN'] ?? false);
            } elseif ($model) {
                $datum['logo'] = $this->createFileDataFromModel($model, false, $fileUtils);
            } else {
                $fallbackPath = 'files/showcases/' . $uuidStr . '/';
                if ($uuidStr) {
                    $rootDir = System::getContainer()->getParameter('kernel.project_dir');
                    if (is_dir($rootDir . '/' . $fallbackPath)) {
                        $files = scandir($rootDir . '/' . $fallbackPath);
                        foreach ($files as $f) {
                            if ($f !== '.' && $f !== '..' && is_file($rootDir . '/' . $fallbackPath . $f)) {
                                $datum['logo'] = $this->createFileDataFromFile($fallbackPath . $f, false, $fileUtils, 0, 150, $result['name'], $result['name'], $uuidStr, $arrOptions['directCDN'] ?? false);
                                if ($datum['logo']) break;
                            }
                        }
                    }
                }
            }

            // 3. Process Gallery
            $datum['imageGallery'] = [];
            $imagesLocal = StringUtil::deserialize($result['imageGallery'], true);
            $imagesCDN = StringUtil::deserialize($result['imageGalleryCDN'], true);
            $idx = 0;
            
            // For the gallery, we try to use CDN if available, even if withoutCDN is requested, 
            // as a fallback for missing local files in Contao 5.3.
            $maxCount = max(count($imagesLocal), count($imagesCDN));
            
            for ($i = 0; $i < $maxCount; $i++) {
                $fileData = null;
                $uuidRaw = $imagesLocal[$i] ?? '';
                $uuidVal = StringUtil::deserialize($uuidRaw);
                $uuidStr = $uuidVal ? (C4GUtils::isBinary($uuidVal) ? StringUtil::binToUuid($uuidVal) : $uuidVal) : '';

                // Try CDN first if available and (CDN is requested OR local file missing)
                $cdnPathRaw = $imagesCDN[$i] ?? '';
                $hasCDN = !empty($cdnPathRaw);
                $model = null;
                if ($uuidVal) {
                    $model = FilesModel::findByUuid($uuidVal);
                    if ($model === null && !C4GUtils::isBinary($uuidVal)) {
                        $model = FilesModel::findByUuid(StringUtil::uuidToBin($uuidVal));
                    }
                }

                // Check if local file exists if model is found
                $localFileExists = false;
                if ($model) {
                    $rootDir = System::getContainer()->getParameter('kernel.project_dir');
                    if (file_exists($rootDir . '/' . $model->path)) {
                        $localFileExists = true;
                    }
                }

                if ($hasCDN && ($useCDN || !$model || !$localFileExists)) {
                    $fileData = $this->createFileDataFromFile($cdnPathRaw, false, $fileUtils, 600, 450, ($result['name'] ?? '').$idx, 'Bild '.$idx.': '.($result['name'] ?? ''), $uuidStr, $arrOptions['directCDN'] ?? false);
                // 2. Try same-origin local file/proxy if model exists
                } elseif ($model) {
                    $fileData = $this->createFileDataFromModel($model, false, $fileUtils);
                    // For the form, we want consistent structure like createFileDataFromFile
                    if (!($arrOptions['directCDN'] ?? false)) {
                        $host = $_SERVER['HTTP_HOST'] ?? 'portal.nordsee.digital';
                        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
                        $baseUrl = $scheme . '://' . $host;
                        
                        // Robust path check for same-origin preview
                        $rootDir = System::getContainer()->getParameter('kernel.project_dir');
                        $realPath = $model->path;
                        if (!file_exists($rootDir . '/' . $realPath)) {
                            $alt = str_replace(['{', '}'], '', $realPath);
                            if (file_exists($rootDir . '/' . $alt)) {
                                $realPath = $alt;
                            } else {
                                $alt2 = preg_replace('#showcases/([0-9A-Fa-f\-]{36})/#', 'showcases/{\1}/', $realPath);
                                if (file_exists($rootDir . '/' . $alt2)) {
                                    $realPath = $alt2;
                                }
                            }
                        }
                        
                        $fileData['src'] = $baseUrl . '/' . $realPath . '?v=' . time();
                        $fileData['path'] = $realPath;
                        
                        // Sanity check for small/invalid local files - fallback to same-origin proxy instead of CDN to avoid Tainted Canvas
                        $localExists = file_exists($rootDir . '/' . $realPath);
                        $localSize = $localExists ? filesize($rootDir . '/' . $realPath) : 0;
                        
                        if (!$localExists || $localSize < 100) {
                            if ($hasCDN) {
                                $fileData['src'] = $fileUtils->addUrlToPath($cdnUrl, $cdnPathRaw, 600, 450);
                                $fileData['path'] = $fileData['src'];
                                C4gLogModel::addLogEntry('data-model', "ShowcaseResultConverter: Local file missing or broken. Falling back to CDN for index $idx: " . $fileData['src']);
                            } else {
                                // Last resort: same-origin proxy (deprecated if no CDN available)
                                $proxyBase = 'files/con4gis_import_data/images/';
                                $scUuidRaw = $result['uuid'] ?? '';
                                $scUuid = strtoupper(str_replace(['{', '}'], '', $scUuidRaw));
                                
                                $proxyFile = '';
                                $filename = '';
                                $ext = 'jpg';
                                
                                // Try to build from image model
                                if ($model) {
                                    $pathParts = pathinfo($model->path);
                                    $filename = $pathParts['filename'];
                                    $ext = $pathParts['extension'] ?? 'jpg';
                                }
                                
                                if (!empty($filename) && !empty($scUuid)) {
                                    $proxyFile = 'showcases/{' . $scUuid . '}/' . $filename . '-small.' . $ext;
                                    $fileData['src'] = $baseUrl . '/' . $proxyBase . $proxyFile . '?v=' . time();
                                    C4gLogModel::addLogEntry('data-model', "ShowcaseResultConverter: Using same-origin proxy URL for index $idx: " . $fileData['src']);
                                }
                            }
                        }
                        
                        C4gLogModel::addLogEntry('data-model', "ShowcaseResultConverter: Gallery index $idx Same-Origin URL: " . $fileData['src'] . " (Resolved path: $realPath)");
                    }
                }

                // 3. Final Fallback to CDN if nothing found yet
                if (!$fileData && $hasCDN) {
                    $fileData = $this->createFileDataFromFile($cdnPathRaw, false, $fileUtils, 600, 450, ($result['name'] ?? '').$idx, 'Bild '.$idx.': '.($result['name'] ?? ''), $uuidStr, $arrOptions['directCDN'] ?? false);
                }

                // 4. Last resort: check if file exists on disk based on naming convention
                if (!$fileData && $uuidStr) {
                    $rootDir = System::getContainer()->getParameter('kernel.project_dir');
                    $uuidVariants = [$uuidStr, '{' . str_replace(['{', '}'], '', $uuidStr) . '}', str_replace(['{', '}'], '', $uuidStr)];
                    foreach ($uuidVariants as $uv) {
                        if (empty($uv)) continue;
                        $fallbackPath = 'files/showcases/' . $uv . '/';
                        if (is_dir($rootDir . '/' . $fallbackPath)) {
                            $files = scandir($rootDir . '/' . $fallbackPath);
                            foreach ($files as $f) {
                                if ($f !== '.' && $f !== '..' && is_file($rootDir . '/' . $fallbackPath . $f) && strpos($f, '.') !== 0) {
                                    $fileData = $this->createFileDataFromFile($fallbackPath . $f, false, $fileUtils, 600, 450, ($result['name'] ?? '').$idx, 'Bild '.$idx.': '.($result['name'] ?? ''), $uuidStr, $arrOptions['directCDN'] ?? false);
                                    if ($fileData) {
                                        C4gLogModel::addLogEntry('data-model', "ShowcaseResultConverter: Gallery index $idx resolved via physical scan: " . ($fileData['src'] ?? 'none'));
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                }
                
                if ($fileData) {
                    $datum['imageGallery_' . $idx] = $fileData;
                    $datum['imageGallery'][] = $fileData;
                    $idx++;
                }
            }
            C4gLogModel::addLogEntry('data-model', "ShowcaseResultConverter: Final gallery count: " . $idx);
            // --- End Image Processing ---

            // load imprint data
            $selectImprintSql = 'SELECT * FROM tl_gutesio_data_element_imprint WHERE `showcaseId` = ?';
            $arrImprintData = $db->prepare($selectImprintSql)->execute($datum['uuid'])->fetchAssoc();
            if ($arrImprintData) {
                $filledImprintData = [];
                foreach ($arrImprintData as $key => $value) {
                    if ($value && !in_array($key, [
                            'id',
                            'uuid',
                            'tstamp',
                            'showcaseId',
                        ])
                    ) {
                        $filledImprintData[$key] = $value;
                    }
                }

                if ($checker->checkIfImprintIsComplete($filledImprintData)) {
                    $filledImprintData['addressStreetAll'] = $filledImprintData['addressStreet'] . ' ' . $arrImprintData['addressStreetNumber'];
                    $filledImprintData['addressCityAll'] = $filledImprintData['addressZipcode'] . ' ' . $arrImprintData['addressCity'];
                    $filledImprintData['responsibleStreetAll'] = $filledImprintData['responsibleStreet'] . ' ' . $arrImprintData['responsibleStreetNumber'];
                    $filledImprintData['responsibleCityAll'] = $filledImprintData['responsibleZipcode'] . ' ' . $arrImprintData['responsibleCity'];
                    if (key_exists('companyForm', $filledImprintData) && $filledImprintData['companyForm'] !== 'noImprintRequired') {
                        $datum['imprintData'] = $filledImprintData;
                    }
                    $datum = array_merge($datum, $filledImprintData);
                } else {
                    // still load data from imprint, even if incomplete
                    $datum = array_merge($datum, $filledImprintData);
                }
            }

            $datum['releaseType'] = $result['releaseType'];
            $datum['foreignLink'] = $result['foreignLink'];
            $datum['directLink'] = $result['foreignLink'] ? '1' : '0';
            $datum['aiOptimized'] = $result['aiOptimized'];

            // rating
            if (isset($result['rating']) && $result['rating'] > 0) {
                $datum['avgRating'] = (float) $result['rating'];
                $datum['countRating'] = (int) ($result['countRating'] ?? 0);
            }

            $datum['extraZip'] = key_exists('extraZip', $result) ? $result['extraZip'] : '';
            $datum['published_title'] = $result['published'] ? 'Ja' : 'Nein'; //ToDo

            $data[] = $datum;
        }

        if (count($data) === 0) {
            return [];
        }

        return count($data) > 1 ? $data : $data[0];
    }

    /**
     * Converts the given model into a frontend representation.
     * @param FilesModel $model
     * @return array
     */
    public function createFileDataFromModel(FilesModel $model, $svg = false, $fileUtils = new FileUtils()) : array
    {
        $rootDir = System::getContainer()->getParameter('kernel.project_dir');
        if ($svg) {
            $width = 100;
            $height = 100;
        } else {
            $path = $model->path;
            list($width, $height) = $fileUtils->getImageSize($path);
            if (!$width || !$height) {
                // Bidirectional fallback for local file size check
                $altPaths = [
                    str_replace(['{', '}'], '', $path),
                    preg_replace('#showcases/([0-9A-Fa-f\-]{36})/#', 'showcases/{\1}/', $path)
                ];
                foreach ($altPaths as $alt) {
                    if ($alt === $path) continue;
                    list($width, $height) = $fileUtils->getImageSize($alt);
                    if ($width && $height) {
                        break;
                    }
                }
            }
        }

        // Add base URL for frontend previews if path is relative
        $src = $model->path;
        if (strpos($src, 'http') === false) {
            // Check if physical file exists with current path, otherwise try alternatives
            $rootDir = System::getContainer()->getParameter('kernel.project_dir');
            if (!file_exists($rootDir . '/' . $src)) {
                $alt1 = str_replace(['{', '}'], '', $src);
                if (file_exists($rootDir . '/' . $alt1)) {
                    $src = $alt1;
                } else {
                    $alt2 = preg_replace('#showcases/([0-9A-Fa-f\-]{36})/#', 'showcases/{\1}/', $src);
                    if (file_exists($rootDir . '/' . $alt2)) {
                        $src = $alt2;
                    }
                }
            }
            
            $proto = 'https://';
            if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'off' || $_SERVER['HTTPS'] === '')) {
                $proto = 'http://';
            }
            $host = $_SERVER['HTTP_HOST'] ?? 'portal.nordsee.digital';
            $src = $proto . rtrim($host, '/') . '/' . ltrim($src, '/');
        }

        return [
            'src' => $src,
            'path' => $model->path, // Keep relative path for backend identification
            'imageData' => $model->path, // Keep relative path
            'uuid' => StringUtil::binToUuid($model->uuid),
            'alt' => $model->meta && unserialize($model->meta)['de'] ? unserialize($model->meta)['de']['alt'] : $model->name,
            'name' => $model->name,
            'height' => $height ?: 450,
            'width' => $width ?: 600,
            'importantPart' => [
                'x' => $model->importantPartX,
                'y' => $model->importantPartY,
                'width' => $model->importantPartWidth,
                'height' => $model->importantPartHeight,
            ],
        ];
    }

    public function createFileDataFromFile($file, $svg = false, $fileUtils = new FileUtils(), $width = 600, $height = 450, $title = '', $alt = '', $uuid = '', $directCDN = false) : array
    {
        if (!$file && !$uuid) {
            return [];
        }

        $objSettings = GutesioOperatorSettingsModel::findSettings();
        $cdnUrl = $objSettings ? $objSettings->cdnUrl : 'https://cdn.con4gis.cloud';
        if (!$cdnUrl) {
            $cdnUrl = 'https://cdn.con4gis.cloud';
        }
        $cdnUrl = rtrim($cdnUrl, '/');

        if ($svg) {
            $width = 100;
            $height = 100;
        }

        $extendedParam = '';
        if ($width == 600) {
            $extendedParam = '-small';
        }

        if ($directCDN) {
            $url = $fileUtils->addUrlToPath($cdnUrl, $file, $width, $height);
            C4gLogModel::addLogEntry('data-model', "ShowcaseResultConverter: Direct CDN URL: " . $url);
            
            // For the preview to work without CORS issues, we use the CDN directly.
            $path = $url;
            $imageData = $url;
        } else {
            // For the form (editing/cropping), we MUST use a Same-Origin URL to avoid 'Tainted Canvas' errors.
            // We try to find the actual local path from the UUID if provided.
            $localFile = null;
            if ($uuid) {
                $objFile = FilesModel::findByUuid($uuid);
                if ($objFile) {
                    $localFile = $objFile->path;
                }
            }
            
            if (!$localFile) {
                // Fallback: derive from CDN path
                $localFile = ltrim($file, '/');
                if (strpos($localFile, 'files/') !== 0) {
                    $localFile = 'files/' . $localFile;
                }
            }
            
            // Robust path check for Contao 5.3
            $rootDir = System::getContainer()->getParameter('kernel.project_dir');
            $fsPath = $rootDir . '/' . $localFile;
            $localValid = (file_exists($fsPath) && filesize($fsPath) > 100);
            
            if (!$localValid) {
                // Try variants ONLY if original is not valid
                $alt1 = preg_replace('#showcases/([0-9A-Fa-f\-]{36})/#', 'showcases/{\1}/', $localFile);
                if (file_exists($rootDir . '/' . $alt1) && filesize($rootDir . '/' . $alt1) > 100) {
                    $localFile = $alt1;
                    $localValid = true;
                } else {
                    $alt2 = str_replace(['{', '}'], '', $localFile);
                    if (file_exists($rootDir . '/' . $alt2) && filesize($rootDir . '/' . $alt2) > 100) {
                        $localFile = $alt2;
                        $localValid = true;
                    }
                }
            }
            
            $host = $_SERVER['HTTP_HOST'] ?? 'portal.nordsee.digital';
            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $baseUrl = $scheme . '://' . $host;
            
            if ($localValid) {
                $url = $baseUrl . '/' . $localFile . '?v=' . time();
                $path = $localFile;
                $imageData = $localFile;
            } else {
                // FALLBACK to CDN URL if local file is missing/broken
                $url = $fileUtils->addUrlToPath($cdnUrl, $file, $width, $height);
                $path = $url;
                $imageData = $url;
                
                C4gLogModel::addLogEntry('data-model', "ShowcaseResultConverter: Local file missing or broken ($localFile). Falling back to CDN: $url");
            }
            
            C4gLogModel::addLogEntry('data-model', "ShowcaseResultConverter: Form URL: " . $url . " (uuid: $uuid)");
        }

        return [
            'src' => $url,
            'path' => $path,
            'imageData' => $imageData,
            'uuid' => $uuid,
            'alt' => $alt,
            'name' => $title,
            'height' => $height,
            'width' => $width,
            'importantPart' => [ // Add dummy important part for consistency
                'x' => 0,
                'y' => 0,
                'width' => 1,
                'height' => 1,
            ]
        ];
    }

    /**
     * Converts showcase data into the option format for select fields.
     * @param $arrShowcaseData
     * @return array
     */
    public function convertToOptions($arrShowcaseData) : array
    {
        $data = [];
        foreach ($arrShowcaseData as $showcase) {
            $data[] = [
                'value' => $showcase['uuid'],
                'label' => $showcase['name'],
            ];
        }

        return $data;
    }

    private function loadTypes()
    {
        if (!empty(static::$cachedTypes)) {
            return;
        }
        $db = Database::getInstance();

        $typeResult = $db->prepare("SELECT `id`, `name`, `uuid` FROM tl_gutesio_data_type")
            ->execute()->fetchAllAssoc();

        foreach ($typeResult as $type) {
            static::$cachedTypes[$type['uuid']] = [
                'value' => $type['id'],
                'label' => html_entity_decode($type['name']),
                'uuid' => $type['uuid'],
            ];
        }
    }

    private function loadTags()
    {
        if (!empty(static::$processedTags)) {
            return;
        }
        $fileUtils = new FileUtils();
        $db = Database::getInstance();
        $tagResult = $db->prepare("SELECT * FROM tl_gutesio_data_tag WHERE `published` = 1")
            ->execute()->fetchAllAssoc();

        foreach ($tagResult as $value) {
            $tag = $value;
            $tag['image'] = [];
            if ($tag['imageCDN']) {
                $tag['image'] = $this->createFileDataFromFile($tag['imageCDN'], true, $fileUtils, 100, 100, $tag['name'], $tag['name'], '', true);
            }
            $tag['value'] = $value['uuid'];
            $tag['label'] = html_entity_decode($value['name']);

            static::$processedTags[$value['uuid']] = $tag;
        }
    }
}
