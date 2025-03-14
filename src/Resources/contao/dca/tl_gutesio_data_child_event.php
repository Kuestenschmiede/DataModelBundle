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

$GLOBALS['TL_DCA']['tl_gutesio_data_child_event'] = [
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
        'beginDate' => [
            'sql' => "int NULL"
        ],
        'beginTime' => [
            'sql' => "int NULL"
        ],
        'entryTime' => [
            'sql' => "int NULL"
        ],
        'endDate' => [
            'sql' => "int NULL"
        ],
        'endTime' => [
            'sql' => "int NULL"
        ],
        'expertTimes' => [
            'sql' => 'char(1) NOT NULL default 0'
        ],
        'expertBeginDateTimes' => [
            'sql' => 'TEXT NULL default ' . serialize([])
        ],
        'expertEndDateTimes' => [
            'sql' => 'TEXT NULL default ' . serialize([])
        ],
        'useDifferentLocation' => [
            'sql' => "char(1) NULL"
        ],
        'locationElementId' => [
            'sql' => 'varchar(50) NULL'
        ],
        'importId' => [
            'sql' => "int(20) unsigned NOT NULL default '0'"
        ],
        'recurring' => [
            'sql' => "char(1) NULL"
        ],
        'repeatEach' => [
            'sql' => "varchar(255) NULL"
        ],
        'recurrences' => [
            'sql' => "smallint(5) unsigned NULL"
        ],
        'appointmentUponAgreement' => [
            'sql' => "char(1) NULL"
        ],
        'minPersons' => [
            'sql' => "int NOT NULL default 0"
        ],
        'maxPersons' => [
            'sql' => "int NOT NULL default 0"
        ],
        'eventPrice' => [
            'sql' => "float NULL"
        ],
        'reservationContactEMail' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'reservationContactPhone' => [
            'sql' => "varchar(50) NOT NULL default ''"
        ]
    ],
];

