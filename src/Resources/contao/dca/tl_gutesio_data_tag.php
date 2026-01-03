<?php
/**
 * This file is part of con4gis,
 * the gis-kit for Contao CMS.
 *
 * @package   	con4gis
 * @author  	    con4gis contributors (see "authors.md")
 * @license 	    LGPL-3.0-or-later
 * @copyright 	Küstenschmiede GmbH Software & Design
 * @link              https://www.con4gis.org
 *
 */


$GLOBALS['TL_DCA']['tl_gutesio_data_tag'] = [
    'config' => [
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'uuid' => 'index'
            ],
        ],
    ],
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
        'name' => [
            'sql' => "varchar(100) NOT NULL default ''"
        ],
        'technicalKey' => [
            'sql' => "varchar(32) NOT NULL default ''"
        ],
        'image' => [
            'sql' => "binary(16) NULL"
        ],
        'imageCDN' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'description' => [
            'sql' => "text NULL"
        ],
        'locstyle' => [
            'sql' => "int unsigned NOT NULL default 0"
        ],
        'postals' => [
            'sql' => "TEXT NULL"
        ],
        'fixedIconUrl' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'setsTileClass' => [
            'sql' => "int unsigned NOT NULL default 0"
        ],
        'validFrom' => [
            'sql' => "int NULL"
        ],
        'validUntil' => [
            'sql' => "int NULL"
        ],
        'importId' => [
            'sql' => "int(20) unsigned NOT NULL default '0'"
        ],
        'published' => [
            'sql' => "int NOT NULL default 1"
        ]
    ],
];
