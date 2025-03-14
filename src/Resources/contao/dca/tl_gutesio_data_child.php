<?php
/**
 * This file is part of con4gis,
 * the gis-kit for Contao CMS.
 *
 * @package   	con4gis
 * @version        7
 * @author  	    con4gis contributors (see "authors.txt")
 * @license 	    LGPL-3.0-or-later
 * @copyright 	Küstenschmiede GmbH Software & Design
 * @link              https://www.con4gis.org
 *
 */

$GLOBALS['TL_DCA']['tl_gutesio_data_child'] = [
    'config' => [
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'uuid' => 'index',
                'parentChildId' => 'index'
            ],
        ],
    ],
    'fields' => [
        'id' => [
            'sql' => 'int unsigned NOT NULL auto_increment',
        ],
        'parentChildId' => [
            'sql' => 'varchar(50) NOT NULL default \'\''
        ],
        'compositionIds' => [
            'sql' => 'TEXT NULL'
        ],
        'uuid' => [
            'sql' => 'varchar(50) NOT NULL default \'\''
        ],
        'tstamp' => [
            'sql' => "int NOT NULL default 0"
        ],
        'typeId' => [
            'sql' => 'varchar(50) NOT NULL default \'\''
        ],
        'name' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'shortDescription' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'description' => [
            'sql' => "TEXT NULL'"
        ],
        'metaDescription' => [
            'sql' => "text NULL"
        ],
        'foreignKey' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'foreignLink' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'directLink' => [
            'sql' => "char(1) NOT NULL default 0"
        ],
        'fullTextContent' => [
            'sql' => "TEXT NULL"
        ],
        'operatingAliases' => [
            'sql' => "TEXT NULL"
        ],
        'publishFrom' => [
            'sql' => "int NULL"
        ],
        'publishUntil' => [
            'sql' => "int NULL"
        ],
        'avgRating' => [
            'sql' => "float(5,2) NOT NULL default 0"
        ],
        'imageCDN' => [
           'sql' => "varchar(255) NOT NULL default ''"
        ],
        'imageGalleryCDN' => [
            'sql' => "blob NULL"
        ],
        'imageCredits' => [
            'sql' => "TEXT NULL"
        ],
        'videoLink' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'videoType' => [
            'sql' => "varchar(10) NOT NULL default ''"
        ],
        'videoPreviewImageCDN' => [
            'sql' => "varchar(255) NULL"
        ],
        'importId' => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'published' => [
            'sql' => "char(1) NOT NULL default 0"
        ],
        'memberId' => [
            'sql' => "int(10) unsigned NOT NULL default 0"
        ],
        'infoFileCDN' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'offerForSale' => [
            'sql' => "char(1) NULL"
        ],
        'childIds' => [
            'sql' => "TEXT NULL"
        ],
        'source' => [
            'sql' => "TEXT NULL"
        ]
    ]
];

