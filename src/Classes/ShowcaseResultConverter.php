<?php
/**
 * This file belongs to gutes.digital and is published exclusively for use
 * in gutes.digital operator or provider pages.

 * @package    gutesio
 * @copyright  Küstenschmiede GmbH Software & Design (Matthias Eilers)
 * @link       https://gutes.digital
 */
namespace gutesio\DataModelBundle\Classes;

use con4gis\CoreBundle\Classes\C4GUtils;
use Contao\Controller;
use Contao\Database;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\System;

/**
 * Class ShowcaseResultConverter
 * @package gutesio\DataModelBundle\Classes
 */
class ShowcaseResultConverter
{
    private $cachedTypes = [];

    private $processedTags = [];

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
    public function convertDbResult($arrResult, $arrOptions = []) : array
    {
        $db = Database::getInstance();
        $checker = new ImprintConstraintChecker();
        System::loadLanguageFile('field_translations');
        $data = [];
        if (count($arrResult) === 0) {
            // no data
            return [];
        }
        foreach ($arrResult as $result) {
            $datum = [];
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
            $datum['description'] = html_entity_decode(Controller::replaceInsertTags($result['description']));
            $datum['directions'] = html_entity_decode($result['directions']);
            $datum['surroungdings'] = html_entity_decode($result['surroungdings']);
            $datum['importantNotes'] = html_entity_decode($result['importantNotes']);
            $datum['alias'] = $result['alias'];
            $datum['geox'] = $result['geox'];
            $datum['geoy'] = $result['geoy'];
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
            $datum['twitter'] = C4GUtils::addProtocolToLink($result['twitter']);
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
            if ($result['videoPreviewImage']) {
                $model = FilesModel::findByUuid(StringUtil::deserialize($result['videoPreviewImage']));
                if ($model !== null) {
                    $datum['videoPreview']['videoPreviewImage'] = $this->createFileDataFromModel($model);
                    $datum['videoPreviewImage'] = $datum['videoPreview']['videoPreviewImage'];
                }
            }
            $datum['youtubeChannelLink'] = C4GUtils::addProtocolToLink($result['youtubeChannelLink']);
            $datum['vimeoChannelLink'] = C4GUtils::addProtocolToLink($result['vimeoChannelLink']);
            $datum['wikipediaLink'] = C4GUtils::addProtocolToLink($result['wikipediaLink']);
            $datum['deviatingPhoneHours'] = $result['deviatingPhoneHours'];
            $datum['phoneHours'] = html_entity_decode($result['phoneHours']);
            $datum['opening_hours'] = html_entity_decode($result['opening_hours']);
            $datum['opening_hours_additional'] = html_entity_decode($result['opening_hours_additional']);
            $datum['legalTextSet'] = $result['legalTextSet'];
            $datum['cashOnlyIfPickup'] = $result['cashOnlyIfPickup'];
            $datum['operators'] = [];

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
            $arrTypeIds = $db
                ->prepare('SELECT typeId FROM tl_gutesio_data_element_type WHERE elementId = ?')
                ->execute($result['uuid'])->fetchEach('typeId');
            foreach ($arrTypeIds as $typeId) {
                if (key_exists($typeId,$this->cachedTypes)) {
                    $datum['types'][] = $this->cachedTypes[$typeId];
                } else {
                    $typeRow = $db
                        ->prepare('SELECT `id`, `name`, `uuid` FROM tl_gutesio_data_type WHERE uuid = ?')
                        ->execute($typeId)->fetchAssoc();

                    $type = false;
                    if ($typeRow) {
                        $value = key_exists('id', $typeRow) ? $typeRow['id'] : '';
                        $label = key_exists('name',$typeRow) ? html_entity_decode($typeRow['name']) : '';
                        $uuid = key_exists('id',$typeRow) ? $typeRow['uuid'] : '';

                        $type = [
                            'value' => $value,
                            'label' => $label,
                            'uuid' => $uuid,
                        ];
                    }

                    if ($type) {
                        $this->cachedTypes[$typeId] = $type;

                        $datum['types'][] = $type;
                    }
                }
            }

            $dietLabels = $GLOBALS['TL_LANG']['gutesio']['diet_options'];
            $cuisineLabels = $GLOBALS['TL_LANG']['gutesio']['cuisine_options'];
            $otherLabels = $GLOBALS['TL_LANG']['gutesio'];
            $otherFields = [
                'selfHelpFocus',
                'contactInfoAdviceFocus',
            ];
            // load type values
            $sql = 'SELECT `typeFieldKey`, `typeFieldValue`, `typeFieldFile` FROM tl_gutesio_data_type_element_values WHERE elementId = ?';
            $typeElementValues = $db->prepare($sql)->execute($datum['uuid'])->fetchAllAssoc();
            foreach ($typeElementValues as $typeElementValue) {
                // check if the value is serialized
                $fieldKey = $typeElementValue['typeFieldKey'];
                $fieldValue = StringUtil::deserialize($typeElementValue['typeFieldValue']);
                if (is_array($fieldValue) && $arrOptions['details']) {
                    $resultValue = '';
                    foreach ($fieldValue as $key => $value) {
                        if (is_array($value)) {
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
                    if ($typeElementValue['typeFieldFile']) {
                        if (C4GUtils::isBinary($typeElementValue['typeFieldFile'])) {
                            $uuid = StringUtil::binToUuid($typeElementValue['typeFieldFile']);
                        } else {
                            $uuid = $typeElementValue['typeFieldFile'];
                        }
                        $fileModel = FilesModel::findByUuid($uuid);
                        if ($fileModel) {
                            $datum[$fieldKey] = [
                                'data' => [],
                                'name' => $fileModel->name,
                                'changed' => false,
                                'path' => $fileModel->path,
                            ];
                        }
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
                if ($this->processedTags[$tagId] && !$arrOptions['loadTagsComplete']) {
                    $datum['tags'][] = $this->processedTags[$tagId];
                } else {
                    if ($arrOptions['loadTagsComplete']) {
                        $tagRow = $db
                            ->prepare('SELECT * FROM tl_gutesio_data_tag WHERE published = 1 AND uuid = ?')
                            ->execute($tagId)->fetchAssoc();
                        $tag = $tagRow;
                        $validFrom = intval($tag['validFrom']);
                        $validUntil = intval($tag['validUntil']);
                        if (($validFrom === 0) || ($validFrom >= time()) && (($validUntil === 0) || ($validUntil <= time()))) {
                            if ($tag['image']) {
                                $filesModel = FilesModel::findByUuid(StringUtil::binToUuid($tag['image']));
                                if ($filesModel) {
                                    $tag['image'] = $this->createFileDataFromModel($filesModel, true);
                                } else {
                                    $tag['image'] = [];
                                }
                                switch ($tag['technicalKey']) {
                                    case 'tag_delivery':
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tagLink = $stmt->execute(
                                            $datum['uuid'],
                                            'deliveryServiceLink'
                                        )->fetchAssoc()['tagFieldValue'];
                                        $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                                        $tag['linkLabel'] = 'Lieferservice';

                                        break;
                                    case 'tag_online_reservation':
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tagLink = $stmt->execute(
                                            $datum['uuid'],
                                            'onlineReservationLink'
                                        )->fetchAssoc()['tagFieldValue'];
                                        if (strpos($tagLink, '@') !== false) {
                                            if (strpos($tagLink, 'mailto:') !== 0) {
                                                $tag['linkHref'] = 'mailto:' . $tagLink;
                                            }
                                        } else {
                                            $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                                        }
                                        $tag['linkLabel'] = 'Onlinereservierung';

                                        break;
                                    case 'tag_clicknmeet':
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tagLink = $stmt->execute(
                                            $datum['uuid'],
                                            'clicknmeetLink'
                                        )->fetchAssoc()['tagFieldValue'];
                                        $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                                        $tag['linkLabel'] = 'Click & Meet';

                                        break;
                                    case 'tag_table_reservation':
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tagLink = $stmt->execute(
                                            $datum['uuid'],
                                            'tableReservationLink'
                                        )->fetchAssoc()['tagFieldValue'];
                                        $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                                        $tag['linkLabel'] = 'Tischreservierung';

                                        break;
                                    case 'tag_onlineshop':
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tagLink = $stmt->execute(
                                            $datum['uuid'],
                                            'onlineShopLink'
                                        )->fetchAssoc()['tagFieldValue'];
                                        $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                                        $tag['linkLabel'] = 'Onlineshop';

                                        break;
                                    case 'tag_help_support':
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tagLink = $stmt->execute(
                                            $datum['uuid'],
                                            'helpSupport'
                                        )->fetchAssoc()['tagFieldValue'];
                                        $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                                        $tag['linkLabel'] = 'Hilfe / Support';

                                        break;
                                    case 'tag_discussion_forum':
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tagLink = $stmt->execute(
                                            $datum['uuid'],
                                            'discussionForum'
                                        )->fetchAssoc()['tagFieldValue'];
                                        $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                                        $tag['linkLabel'] = 'Diskussionsforum';

                                        break;
                                    case 'tag_ios_app':
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tagLink = $stmt->execute(
                                            $datum['uuid'],
                                            'iosApp'
                                        )->fetchAssoc()['tagFieldValue'];
                                        $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                                        $tag['linkLabel'] = 'iOS-App';

                                        break;
                                    case 'tag_android_app':
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tagLink = $stmt->execute(
                                            $datum['uuid'],
                                            'androidApp'
                                        )->fetchAssoc()['tagFieldValue'];
                                        $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                                        $tag['linkLabel'] = 'Android-App';

                                        break;
                                    case 'tag_online_counseling':
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tagLink = $stmt->execute(
                                            $datum['uuid'],
                                            'onlineCounseling'
                                        )->fetchAssoc()['tagFieldValue'];
                                        $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                                        $tag['linkLabel'] = 'Online-Beratung';

                                        break;
                                    case 'tag_online_chat':
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tagLink = $stmt->execute(
                                            $datum['uuid'],
                                            'onlineChat'
                                        )->fetchAssoc()['tagFieldValue'];
                                        $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                                        $tag['linkLabel'] = 'Online-Chat';

                                        break;
                                    case 'tag_online_video_forum':
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tagLink = $stmt->execute(
                                            $datum['uuid'],
                                            'onlineVideoForum'
                                        )->fetchAssoc()['tagFieldValue'];
                                        $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                                        $tag['linkLabel'] = 'Online-Videoforum';

                                        break;
                                    case 'tag_online_therapy_program':
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tagLink = $stmt->execute(
                                            $datum['uuid'],
                                            'onlineTherapyProgram'
                                        )->fetchAssoc()['tagFieldValue'];
                                        $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                                        $tag['linkLabel'] = 'Online-Therapieprogramm';

                                        break;
                                    case 'tag_tariff_calculator':
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tagLink = $stmt->execute(
                                            $datum['uuid'],
                                            'tariffCalculator'
                                        )->fetchAssoc()['tagFieldValue'];
                                        $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                                        $tag['linkLabel'] = 'Tarifrechner';

                                        break;
                                    case 'tag_donation':
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tagLink = $stmt->execute(
                                            $datum['uuid'],
                                            'donationLink'
                                        )->fetchAssoc()['tagFieldValue'];
                                        $tag['linkHref'] = C4GUtils::addProtocolToLink($tagLink);
                                        $tag['linkLabel'] = 'Spendenlink';

                                        break;
                                    default:
                                        break;
                                }
                            }
                            if ($tag) {
                                $tag['value'] = $tagId;
                                $this->processedTags[$tagId] = $tag;
                                $datum['tags'][] = $tag;
                            }
                        }
                    } else {
                        $tagRow = $db
                            ->prepare('SELECT `id`, `name`, `validFrom`, `validUntil` FROM tl_gutesio_data_tag WHERE published = 1 AND uuid = ?')
                            ->execute($tagId)->fetchAssoc();
                        $validFrom = intval($tagRow['validFrom']);
                        $validUntil = intval($tagRow['validUntil']);
                        if (($validFrom === 0) || ($validFrom >= time())
                            && (($validUntil === 0) || ($validUntil <= time()))
                        ) {
                            $tag = [
                                'value' => $tagId,
                                'label' => html_entity_decode($tagRow['name']),
                            ];
                            if ($tag) {
                                $this->processedTags[$tagId] = $tag;
                                $datum['tags'][] = $tag;
                            }
                        }
                    }
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
                if (!$datum[$tagElementValue['tagFieldKey']]) {
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
                                    if ($showcase['allowLogoDisplay'] && $showcase['logo'] && !in_array($showcase['uuid'], $processedIds)) {
                                        $logoModel = FilesModel::findByUuid(StringUtil::binToUuid($showcase['logo']));
                                        if ($logoModel) {
                                            if (!$datum['relatedShowcaseLogos']) {
                                                $datum['relatedShowcaseLogos'] = [];
                                            }
                                            // this array is needed to restrict the options for the type filter correctly
                                            if (!$datum['relatedShowcases']) {
                                                $datum['relatedShowcases'] = [];
                                            }
                                            $logoData = $this->createFileDataFromModel($logoModel);
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
            if ($result['directory']) {
                $datum['directory'] = $result['directory'];
            }

            if ($result['image']) {
                $model = FilesModel::findByUuid(StringUtil::deserialize($result['image']));
                if ($model !== null) {
                    $datum['image'] = $this->createFileDataFromModel($model);
                }
            }
            if ($result['imageList']) {
                $model = FilesModel::findByUuid(StringUtil::deserialize($result['imageList']));
                if ($model !== null) {
                    $datum['imageList'] = $this->createFileDataFromModel($model);
                }
            }
            if ($result['imageShowcase']) {
                $model = FilesModel::findByUuid(StringUtil::deserialize($result['imageShowcase']));
                if ($model !== null) {
                    $datum['imageShowcase'] = $this->createFileDataFromModel($model);
                }
            }
            if ($result['imagePopup']) {
                $model = FilesModel::findByUuid(StringUtil::deserialize($result['imagePopup']));
                if ($model !== null) {
                    $datum['imagePopup'] = $this->createFileDataFromModel($model);
                }
            }
            if ($result['logo']) {
                $model = FilesModel::findByUuid(StringUtil::deserialize($result['logo']));
                if ($model !== null) {
                    $datum['logo'] = $this->createFileDataFromModel($model);
                }
            }
            if ($result['imageGallery']) {
                $images = StringUtil::deserialize($result['imageGallery']);
                $idx = 0;
                foreach ($images as $image) {
                    $model = FilesModel::findByUuid(StringUtil::deserialize($image));
                    if ($model !== null) {
                        $datum['imageGallery_' . $idx] = $this->createFileDataFromModel($model);
                        $idx++;
                    }
                }
            }
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
                    if ($filledImprintData['companyForm'] !== 'noImprintRequired') {
                        $datum['imprintData'] = $filledImprintData;
                    }
                    $datum = array_merge($datum, $filledImprintData);
                }
            }

            $datum['releaseType'] = $result['releaseType'];
            $datum['foreignLink'] = $result['foreignLink'];
            $datum['directLink'] = $result['foreignLink'] ? '1' : '0';
            $datum['extraZip'] = key_exists('extraZip', $result) ? $result['extraZip'] : '';
            $datum['published_title'] = $result['published'] ? 'Ja' : 'Nein'; //ToDo

            $data[] = $datum;
        }

        return count($data) > 1 ? $data : $data[0];
    }

    /**
     * Converts the given model into a frontend representation.
     * @param FilesModel $model
     * @return array
     */
    public function createFileDataFromModel(FilesModel $model, $svg = false) : array
    {
        if ($svg) {
            $width = 100;
            $height = 100;
        } else {
            list($width, $height) = getimagesize($model->path);
        }

        return [
            'src' => $model->path,
            'path' => $model->path,
            'uuid' => StringUtil::binToUuid($model->uuid),
            'alt' => $model->meta && unserialize($model->meta)['de'] ? unserialize($model->meta)['de']['alt'] : $model->name,
            'name' => $model->name,
            'height' => $height,
            'width' => $width,
            'importantPart' => [
                'x' => $model->importantPartX,
                'y' => $model->importantPartY,
                'width' => $model->importantPartWidth,
                'height' => $model->importantPartHeight,
            ],
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
}
