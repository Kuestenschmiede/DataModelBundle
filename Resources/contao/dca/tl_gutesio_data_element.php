<?php
/**
 * This file is part of con4gis,
 * the gis-kit for Contao CMS.
 *
 * @package   	con4gis
 * @version        6
 * @author  	    con4gis contributors (see "authors.txt")
 * @license 	    LGPL-3.0-or-later
 * @copyright 	Küstenschmiede GmbH Software & Design
 * @link              https://www.con4gis.org
 *
 */


$GLOBALS['TL_DCA']['tl_gutesio_data_element'] = [
    // Config
    'config' => [
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'uuid' => 'index'
            ],
        ],
    ],
    
    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int unsigned NOT NULL auto_increment',
        ],
        'uuid' => [
            'sql' => 'varchar(50) NOT NULL default \'\''
        ],
        'tstamp' => [
            'sql' => "int NOT NULL default 0"
        ],
        'parentElementId' => [
            'sql' => 'varchar(50) NOT NULL default \'\''
        ],
        'showcaseIds' => [
            'sql' => "TEXT NULL"
        ],
        'name' => [
            'sql' => "varchar(64) NOT NULL default ''"
        ],
        'description' => [
            'sql' => "text NULL"
        ],
        'metaDescription' => [
            'sql' => "text NULL"
        ],
        'alias' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'postalRadius' => [
            'sql' => "TEXT NULL"
        ],
        'locstyle' => [
            'sql' => "int unsigned NOT NULL default 0"
        ],
        'geox' => [
            'sql' => "varchar(20) NOT NULL default ''"
        ],
        'geoy' => [
            'sql' => "varchar(20) NOT NULL default ''"
        ],
        'geojson' => [
            'sql' => "TEXT NULL"
        ],
        'directions' => [
            'sql' => "TEXT NULL"
        ],
        'importantNotes' => [
            'sql' => "TEXT NULL"
        ],
        'contactable' => [
            'sql' => "tinyint(1) NOT NULL default 0"
        ],
        'contactName' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'contactAdditionalName' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'contactStreet' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'contactStreetNumber' => [
            'sql' => "varchar(10) NOT NULL default ''"
        ],
        'contactZip' => [
            'sql' => "varchar(10) NOT NULL default ''"
        ],
        'contactCity' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'locationStreet' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'locationStreetNumber' => [
            'sql' => "varchar(10) NOT NULL default ''"
        ],
        'locationStreetSuffix' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'locationZip' => [
            'sql' => "varchar(10) NOT NULL default ''"
        ],
        'locationCity' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'phone' => [
            'sql' => "varchar(50) NOT NULL default ''"
        ],
        'fax' => [
            'sql' => "varchar(50) NOT NULL default ''"
        ],
        'mobile' => [
            'sql' => "varchar(50) NOT NULL default ''"
        ],
        'email' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'contactPhone' => [
            'sql' => "varchar(50) NOT NULL default ''"
        ],
        'contactFax' => [
            'sql' => "varchar(50) NOT NULL default ''"
        ],
        'contactMobile' => [
            'sql' => "varchar(50) NOT NULL default ''"
        ],
        'contactEmail' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'website' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'wikipediaLink' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'facebook' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'twitter' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'instagram' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'xing' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'linkedin' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'whatsapp' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'youtubeChannelLink' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'vimeoChannelLink' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'videoLink' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'videoType' => [
            'sql' => "varchar(10) NOT NULL default ''"
        ],
        'videoPreviewImage' => [
            'sql' => "binary(16) NULL"
        ],
        'opening_hours' => [
            'sql' => "TEXT NULL"
        ],
        'opening_hours_additional' => [
            'sql' => "TEXT NULL"
        ],
        'image' => [
            'sql' => "binary(16) NULL"
        ],
        'imagePopup' => [
            'sql' => "binary(16) NULL"
        ],
        'imageShowcase' => [
            'sql' => "binary(16) NULL"
        ],
        'imageList' => [
            'sql' => "binary(16) NULL"
        ],
        'logo' => [
            'sql' => "binary(16) NULL"
        ],
        'allowLogoDisplay' => [
            'sql' => "int NOT NULL default 0"
        ],
        'imageGallery' => [
            'sql' => "blob NULL"
        ],
        'video' => [
            'sql' => "binary(16) NULL"
        ],
        'virtualTour' => [
            'sql' => "binary(16) NULL"
        ],
        'ownerGroupId' => [
            'sql' => "int NOT NULL default 0"
        ],
        'ownerMemberId' => [
            'sql' => "int NOT NULL default 0"
        ],
        'clickCollect' => [
            'sql' => "int NOT NULL default 0"
        ],
        'published' => [
            'sql' => "int NOT NULL default 0"
        ],
        'publishFrom' => [
            'sql' => "int NULL"
        ],
        'publishUntil' => [
            'sql' => "int NULL"
        ],
        'operatingAliases' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'preferredOperator' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'preferredOperatorRights' => [
            'sql' => "TEXT NULL"
        ],
        'osmId' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'foundingDate' => [
            'sql' => 'int NULL'
        ],
        'addedOn' => [
            'sql' => "int NULL"
        ],
        'deletedOn' => [
            'sql' => "int NULL"
        ],
        'foreignLink' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'releaseType' => [
            'sql' => "varchar(13) NOT NULL default ''"
        ],
        'importId' => [
            'sql' => "int(20) unsigned NOT NULL default '0'"
        ]
    ],
];