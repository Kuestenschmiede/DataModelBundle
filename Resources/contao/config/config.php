<?php

use gutesio\DataModelBundle\Resources\contao\models\GutesioDataDirectoryModel;
use gutesio\DataModelBundle\Resources\contao\models\GutesioDataDirectoryTypeModel;
use gutesio\DataModelBundle\Resources\contao\models\GutesioDataElementModel;
use gutesio\DataModelBundle\Resources\contao\models\GutesioDataElementTypeModel;
use gutesio\DataModelBundle\Resources\contao\models\GutesioDataElementOperatorContactModel;
use gutesio\DataModelBundle\Resources\contao\models\GutesioDataTagElementModel;
use gutesio\DataModelBundle\Resources\contao\models\GutesioDataOperatorModel;
use gutesio\DataModelBundle\Resources\contao\models\GutesioDataRatingModel;
use gutesio\DataModelBundle\Resources\contao\models\GutesioDataTypeModel;
use gutesio\DataModelBundle\Resources\contao\models\GutesioDataTagModel;
use gutesio\DataModelBundle\Resources\contao\models\GutesioDataChildModel;
use gutesio\DataModelBundle\Resources\contao\models\GutesioDataChildProductModel;
use gutesio\DataModelBundle\Resources\contao\models\GutesioDataChildEventModel;
use gutesio\DataModelBundle\Resources\contao\models\GutesioDataChildJobModel;
use gutesio\DataModelBundle\Resources\contao\models\GutesioDataChildTypeModel;
use gutesio\DataModelBundle\Resources\contao\models\GutesioDataChildApiModel;

$GLOBALS['TL_MODELS']['tl_gutesio_data_directory'] = GutesioDataDirectoryModel::class;
$GLOBALS['TL_MODELS']['tl_gutesio_data_directory_type'] = GutesioDataDirectoryTypeModel::class;
$GLOBALS['TL_MODELS']['tl_gutesio_data_element'] = GutesioDataElementModel::class;
$GLOBALS['TL_MODELS']['tl_gutesio_data_element_type'] = GutesioDataElementTypeModel::class;
$GLOBALS['TL_MODELS']['tl_gutesio_data_element_operator_contact'] = GutesioDataElementOperatorContactModel::class;
$GLOBALS['TL_MODELS']['tl_gutesio_data_element_tag'] = GutesioDataTagElementModel::class;
$GLOBALS['TL_MODELS']['tl_gutesio_data_operator'] = GutesioDataOperatorModel::class;
$GLOBALS['TL_MODELS']['tl_gutesio_data_rating'] = GutesioDataRatingModel::class;
$GLOBALS['TL_MODELS']['tl_gutesio_data_type'] = GutesioDataTypeModel::class;
$GLOBALS['TL_MODELS']['tl_gutesio_data_tag'] = GutesioDataTagModel::class;
$GLOBALS['TL_MODELS']['tl_gutesio_data_child'] = GutesioDataChildModel::class;
$GLOBALS['TL_MODELS']['tl_gutesio_data_child_product'] = GutesioDataChildProductModel::class;
$GLOBALS['TL_MODELS']['tl_gutesio_data_child_event'] = GutesioDataChildEventModel::class;
$GLOBALS['TL_MODELS']['tl_gutesio_data_child_job'] = GutesioDataChildJobModel::class;
$GLOBALS['TL_MODELS']['tl_gutesio_data_child_type'] = GutesioDataChildTypeModel::class;
$GLOBALS['TL_MODELS']['tl_gutesio_data_child_api'] = GutesioDataChildApiModel::class;


$GLOBALS['gutesio']['diet_options'] = [
    'meat',
    'gluten_free',
    'halal',
    'kosher',
    'lactose_free',
    'vegan',
    'vegetarian'
];

$GLOBALS['gutesio']['cuisine_options'] = [
    'noodle', //Asiatische Nudeln
    'casserole', //Aufläufe
    'sausages', //Bratwurst
    'baquette', //Baguette
    'burger', //Burger
    'curry', //Curry Gerichte
    'kebab', //Döner Kebab
    'seafood', //Fisch und Meeresfrüchte
    'fried_food', //Fritiertes
    'chicken', //Geflügel
    'barbecue', //Gegrilltes
    'gyros', //Gyros
    'hot_dog', //Hot dog
    'pasta', //Italienische Nudeln
    'pizza', //Pizza
    'regional', //Regional
    'sandwich', //Sandwiches
    'soup', //Suppen
    'steak', //Steak
    'sushi', //Sushi
    'tapas' //Tapas
];

