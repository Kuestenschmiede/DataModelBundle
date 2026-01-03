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


$GLOBALS['TL_DCA']['tl_gutesio_data_rating'] = [
    'config' => [
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'uuid' => 'index',
                'elementId' => 'index',
                'childId' => 'index',
                'memberId' => 'index'
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
        'elementId' => [
            'sql' => "varchar(50) NOT NULL default ''"
        ],
        'childId' => [
            'sql' => "varchar(50) NOT NULL default ''"
        ],
        'memberId' => [
            'sql' => "int(10) unsigned NOT NULL default 0"
        ],
        'ratingValue' => [
            'sql'=> "int NOT NULL default 0"
        ],
        'ratingNotice' => [
            'sql' => "text NULL"
        ],
        'importId' => [
            'sql' => "int(20) unsigned NOT NULL default '0'"
        ]
    ],
];