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

$GLOBALS['TL_DCA']['tl_gutesio_data_child_voucher'] = [
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
        'customizableCredit' => [
            'sql' => "char(1) NOT NULL default ''"
        ],
        'credit' => [
            'sql' => "int NOT NULL default 0"
        ],
        'minCredit' => [
            'sql' => "int NOT NULL default 0"
        ],
        'maxCredit' => [
            'sql' => "int NOT NULL default 0"
        ],
        'interval' => [
            'sql' => "int NOT NULL default 1"
        ],
        'primaryColour' => [
            'sql' => "varchar(10) NOT NULL default ''"
        ],
        'secondaryColour' => [
            'sql' => "varchar(10) NOT NULL default ''"
        ],
        'title' => [
            'sql' => "varchar(50) NOT NULL default ''"
        ],
        'abbreviation' => [
            'sql' => "varchar(10) NOT NULL default ''"
        ],
        'text' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'footer' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'logo' => [
            'sql' => "binary(16) NULL"
        ],
        'importId' => [
            'sql' => "int(20) unsigned NOT NULL default '0'"
        ]
    ],
];

