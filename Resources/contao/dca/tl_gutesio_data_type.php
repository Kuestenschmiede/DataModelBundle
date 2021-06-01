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


$GLOBALS['TL_DCA']['tl_gutesio_data_type'] = [
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
        'name' => [
            'sql' => "varchar(100) NOT NULL default ''"
        ],
        'technicalKey' => [
            'sql' => "TEXT NULL"
        ],
        'image' => [
            'sql' => "binary(16) NULL"
        ],
        'description' => [
            'sql' => "text NULL"
        ],
        'locstyle' => [
            'sql' => "int unsigned NOT NULL default 0"
        ],
        'loctype' => [
            'sql' => "varchar(255) NOT NULL default 'POI'"
        ],
        'editorConfig' => [
            'sql' => "int unsigned NOT NULL default 0"
        ],
        'osmKey' => [
            'sql' => "varchar(255) NOT NULL"
        ],
        'osmValue' => [
            'sql' => "varchar(255) NOT NULL"
        ],
        'radiusSettable' => [
            'sql' => "int unsigned NOT NULL default 0"
        ],
        'showLinkedElements' => [
            'sql' => "char(1) NOT NULL default ''"
        ],
        'importId' => [
            'sql' => "int(20) unsigned NOT NULL default '0'"
        ],
        'specialShowcase' => [
            'sql' => "char(1) NOT NULL default ''"
        ],
        'externalShowcase' => [
            'sql' => "char(1) NOT NULL default ''"
        ]
    ],
];
