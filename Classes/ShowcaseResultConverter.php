<?php
/**
 * This file belongs to gutes.io and is published exclusively for use
 * in gutes.io operator or provider pages.

 * @package    gutesio
 * @copyright  Küstenschmiede GmbH Software & Design (Matthias Eilers)
 * @link       https://gutes.io
 */
namespace gutesio\DataModelBundle\Classes;

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

    private $cachedTags = [];

    private $fileUploadFields = [
        TypeFormFieldGenerator::FIELD_BROCHURE_UPLOAD,
        TypeFormFieldGenerator::FIELD_MENU_UPLOAD,
    ];

    private function checkLink($link)
    {
        $result = $link;
        if ($link && (strpos($link, '://') === false)) {
            $result = 'https://' . $link;
        }

        return $result;
    }

    /**
     * Converts the data into the format needed by the client.
     * @param $arrResult
     * @param array $arrOptions
     * @return array
     */
    public function convertDbResult($arrResult, $arrOptions = []) : array
    {
        $db = Database::getInstance();
        System::loadLanguageFile('field_translations');
        $data = [];
        if (count($arrResult) === 0) {
            // no data
            return [];
        }
        foreach ($arrResult as $result) {
            $datum = [];
            // for wishlist
            if ($result['internal_type']) {
                $datum['internal_type'] = $result['internal_type'];
            }
            $datum['name'] = $result['name'];
            $datum['id'] = $result['id'];
            $datum['uuid'] = $result['uuid'];
            $datum['ownerGroupId'] = $result['ownerGroupId'];
            $datum['ownerMemberId'] = $result['ownerMemberId'];
            $datum['description'] = Controller::replaceInsertTags($result['description']);
            $datum['alias'] = $result['alias'];
            $datum['geox'] = $result['geox'];
            $datum['geoy'] = $result['geoy'];
            $datum['geojson'] = $result['geojson'];
            $datum['email'] = $result['email'];
            $datum['phone'] = html_entity_decode($result['phone']);
            $datum['mobile'] = $result['mobile'];
            $datum['website'] = $this->checkLink($result['website']);
            //$datum['websiteLabel'] = $result['websiteLabel'];
            $datum['facebook'] = $this->checkLink($result['facebook']);
            $datum['twitter'] = $this->checkLink($result['twitter']);
            $datum['instagram'] = $this->checkLink($result['instagram']);
            $datum['xing'] = $this->checkLink($result['xing']);
            $datum['linkedin'] = $this->checkLink($result['linkedin']);
            $datum['whatsapp'] = $result['whatsapp'];
            if (strpos($datum['whatsapp'], 'https') === false) {
                // not a link, but a number
                // check if first digit is a 0, that must be stripped out
                if (strpos($datum['whatsapp'], '0') === 0) {
                    $datum['whatsapp'] = substr($datum['whatsapp'], 1);
                    $datum['whatsapp'] = $datum['whatsapp'] ? 'https://wa.me/' . $datum['whatsapp'] : $datum['whatsapp'];
                }
            }
            $datum['published'] = intval($result['published']);
            $datum['allowLogoDisplay'] = intval($result['allowLogoDisplay']);
            $datum['distance'] = $result['distance'];
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
            $datum['youtubeChannelLink'] = $this->checkLink($result['youtubeChannelLink']);
            $datum['vimeoChannelLink'] = $this->checkLink($result['vimeoChannelLink']);
            $datum['wikipediaLink'] = $this->checkLink($result['wikipediaLink']);
            $datum['opening_hours'] = $result['opening_hours'];
            $datum['opening_hours_additional'] = $result['opening_hours_additional'];

            $datum['operators'] = [];

            if ($result['operators'] && is_array($result['operators'])) {
                foreach ($result['operators'] as $operator) {
                    $datum['operators'][] = [
                        'value' => $operator['operatorId'],
                        'label' => $operator['name'],
                    ];
                }
            }

            // load types
            $datum['types'] = [];
            $arrTypeIds = $db
                ->prepare('SELECT typeId FROM tl_gutesio_data_element_type WHERE elementId = ?')
                ->execute($result['uuid'])->fetchEach('typeId');
            foreach ($arrTypeIds as $typeId) {
                if ($this->cachedTypes[$typeId]) {
                    $datum['types'][] = $this->cachedTypes[$typeId];
                } else {
                    $typeRow = $db
                        ->prepare('SELECT `id`, `name` FROM tl_gutesio_data_type WHERE uuid = ?')
                        ->execute($typeId)->fetchAssoc();
                    $type = [
                        'value' => $typeRow['id'],
                        'label' => $typeRow['name'],
                    ];
                    if ($type) {
                        $this->cachedTypes[$typeId] = $type;

                        $datum['types'][] = $type;
                    }
                }
            }

            $dietLabels = $GLOBALS['TL_LANG']['gutesio']['diet_options'];
            $cuisineLabels = $GLOBALS['TL_LANG']['gutesio']['cuisine_options'];
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
                        // array inside array
                        if (is_array($value)) {
                            // $value is an option with label and value
                            // we want to display the label
                            $resultValue .= $value['label'];
                        } else {
                            if ($fieldKey === 'cuisine') {
                                $resultValue .= $cuisineLabels[$value];
                            } elseif ($fieldKey === 'diet') {
                                $resultValue .= $dietLabels[$value];
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
                    $uuid = StringUtil::binToUuid($typeElementValue['typeFieldFile']);
                    $fileModel = FilesModel::findByUuid($uuid);
                    if ($fileModel) {
                        $datum[$fieldKey] = [
                            'data' => [],
                            'name' => $fileModel->name,
                            'changed' => false,
                            'path' => $fileModel->path,
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
                if ($this->cachedTags[$tagId]) {
                    $datum['tags'][] = $this->cachedTags[$tagId];
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
                                    $tag['image'] = $this->createFileDataFromModel($filesModel);
                                } else {
                                    $tag['image'] = [];
                                }
                                switch ($tag['technicalKey']) {
                                    case 'tag_delivery':
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tag['linkHref'] = $stmt->execute(
                                            $datum['uuid'],
                                            'deliveryServiceLink'
                                        )->fetchAssoc()['tagFieldValue'];
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tag['linkLabel'] = 'Lieferservice';

                                        break;
                                    case 'tag_online_reservation':
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tag['linkHref'] = $stmt->execute(
                                            $datum['uuid'],
                                            'onlineReservationLink'
                                        )->fetchAssoc()['tagFieldValue'];
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tag['linkLabel'] = 'Onlinereservierung';

                                        break;
                                    case 'tag_onlineshop':
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tag['linkHref'] = $stmt->execute(
                                            $datum['uuid'],
                                            'onlineShopLink'
                                        )->fetchAssoc()['tagFieldValue'];
                                        $stmt = $db->prepare(
                                            'SELECT tagFieldValue FROM tl_gutesio_data_tag_element_values ' .
                                            'WHERE elementId = ? AND tagFieldKey = ? ORDER BY id ASC');
                                        $tag['linkLabel'] = 'Onlineshop';/*$stmt->execute(
                                        $datum['uuid'],
                                        'onlineShopLinkLabel'
                                    )->fetchAssoc()['tagFieldValue'];*/
                                        break;
                                    default:
                                        break;
                                }
                            }
                            if ($tag) {
                                $this->cachedTags[$tagId] = $tag;
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
                                'value' => $tagRow['id'],
                                'label' => $tagRow['name'],
                            ];
                            if ($tag) {
                                $this->cachedTags[$tagId] = $tag;
                                $datum['tags'][] = $tag;
                            }
                        }
                    }
                }
            }

            // load tag values
            $sql = 'SELECT `tagFieldKey`, `tagFieldValue` FROM tl_gutesio_data_tag_element_values WHERE elementId = ?';
            $tagElementValues = $db->prepare($sql)->execute($datum['uuid'])->fetchAllAssoc();
            foreach ($tagElementValues as $tagElementValue) {
                $datum[$tagElementValue['tagFieldKey']] = $tagElementValue['tagFieldValue'];
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
                    foreach ($showcases as $showcase) {
                        $relatedIds = StringUtil::deserialize($showcase['showcaseIds']);
                        if ($relatedIds) {
                            foreach ($relatedIds as $relatedId) {
                                if ($relatedId === $datum['uuid']) {
                                    if ($showcase['allowLogoDisplay']) {
                                        $logoModel = FilesModel::findByUuid(StringUtil::binToUuid($showcase['logo']));
                                        if ($logoModel) {
                                            $datum['relatedShowcaseLogos_' . $idx] = $this->createFileDataFromModel($logoModel);
                                            $idx++;
                                        }
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

            $datum['contactable'] = $result['contactable'];
            $datum['contactName'] = $result['contactName'];
            $datum['contactAdditionalName'] = $result['contactAdditionalName'];
            if (!$datum['contactName'] && !$datum['contactAdditionalName']) {
                $datum['contactName'] = $datum['name'];
            }
            $datum['contactZip'] = $result['contactZip'];
            $datum['contactCity'] = $result['contactCity'];
            $datum['contactStreet'] = $result['contactStreet'];
            $datum['contactStreetNumber'] = $result['contactStreetNumber'];

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
            $datum['releaseType'] = $result['releaseType'];
            $datum['foreignLink'] = $result['foreignLink'];
            $datum['extraZip'] = $result['extraZip'];
            $datum['published_title'] = $result['published'] === '1' ? 'Ja' : 'Nein';
            $datum['clickCollect'] = $result['clickCollect'] === '1';

            $data[] = $datum;
        }

        return count($data) > 1 ? $data : $data[0];
    }

    /**
     * Converts the given model into a frontend representation.
     * @param FilesModel $model
     * @return array
     */
    public function createFileDataFromModel(FilesModel $model) : array
    {
        return [
            'src' => $model->path,
            'path' => $model->path,
            'uuid' => StringUtil::binToUuid($model->uuid),
            'alt' => $model->meta && unserialize($model->meta)['de'] ? unserialize($model->meta)['de']['alt'] : $model->name,
            'name' => $model->name,
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
