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

$GLOBALS['TL_DCA']['tl_gutesio_data_child_person'] = [
    'config' => [
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'uuid' => 'index',
                'childId' => 'index'
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
        'dateOfBirth' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'dateOfDeath' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ]
    ],
];

