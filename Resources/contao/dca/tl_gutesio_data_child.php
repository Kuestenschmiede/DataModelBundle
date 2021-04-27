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
        'image' => [
            'sql' => "binary(16) NULL"
        ],
        'imageOffer' => [
            'sql' => "binary(16) NULL"
        ],
        'imageGallery' => [
            'sql' => "blob NULL"
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
        'updatedBy' => [
            'sql' => "int NOT NULL default 0"
        ],
        'infoFile' => [
            'sql' => "binary(16) NULL"
        ]
    ],
];

