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

$GLOBALS['TL_DCA']['tl_gutesio_data_child_type'] = [
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
        'parentChildTypeId' => [
            'sql' => 'varchar(50) NOT NULL default \'\''
        ],
        'type' => [
            'sql' => "varchar(32) NOT NULL default ''"
        ],
        'foreignKey' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'foreignKeyList' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'foreignType' => [
            'sql' => "varchar(32) NOT NULL default ''"
        ],
        'name' => [
            'sql' => "varchar(100) NOT NULL default ''"
        ],
        'description' => [
            'sql' => "TEXT NULL'"
        ],
        'sortFields' => [
            'sql' => "TEXT NULL'"
        ],
        'importId' => [
            'sql' => "int(20) unsigned NOT NULL default '0'"
        ],
        'extendedSearchTerms' => [
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'technicalKey' => [
            'sql' => "TEXT NULL"
        ],
    ],
];

