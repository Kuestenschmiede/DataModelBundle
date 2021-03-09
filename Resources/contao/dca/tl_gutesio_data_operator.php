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


$GLOBALS['TL_DCA']['tl_gutesio_data_operator'] = [
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
        'groupId' => [
            'sql' => "int NOT NULL default 0"
        ],
        'memberId' => [
            'sql' => "int NOT NULL default 0"
        ],
        'elementId' => [
            'sql' => 'varchar(50) NOT NULL default \'\''
        ],
        'status' => [
            'sql' => 'varchar(8) NOT NULL default \'\''
        ],
        'releaseNotes' => [
            'sql' => "text NULL"
        ],
        'releasedOn' => [
            'sql' => "int NULL"
        ],
        'refusalNotes' => [
            'sql' => "text NULL"
        ],
        'refusalSubject' => [
            'sql' => "varchar(127) NOT NULL default ''"
        ],
        'refusedOn' => [
            'sql' => "int NULL"
        ],
        'changedOn' => [
            'sql' => "int NULL"
        ],
        'operatingZips' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'jumpStartTariff' => [
            'sql' => "varchar(32) NOT NULL default ''"
        ],
        'jumpStartDurationEnd' => [
            'sql' => "int NULL"
        ],
        'favoredOn' => [
            'sql' => "int NULL"
        ],
        'importId' => [
            'sql' => "int(20) unsigned NOT NULL default '0'"
        ]
    ],
];