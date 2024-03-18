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


$GLOBALS['TL_DCA']['tl_gutesio_data_type_child_values'] = [
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
        'childId' => [
            'sql' => 'varchar(50) NOT NULL default \'\''
        ],
        'typeId' => [
            'sql' => 'varchar(50) NOT NULL default \'\''
        ],
        'typeFieldKey' => [
            'sql' => "varchar(32) NOT NULL default ''"
        ],
        'typeFieldValue' => [
            'sql' => "TEXT NULL"
        ],
        'typeFieldFile' => [
            'sql' => "binary(16) NULL"
        ],
        'typeFieldFileCDN' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'importId' => [
            'sql' => "int(20) unsigned NOT NULL default '0'"
        ]
    ],
];
