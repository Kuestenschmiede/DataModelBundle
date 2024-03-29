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


$GLOBALS['TL_DCA']['tl_gutesio_showcase_statistic'] = [
    'config' => [
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'uuid' => 'index',
                'showcaseId' => 'index'
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
        'date' => [
            'sql' => "int NOT NULL default 0"
        ],
        'showcaseId' => [
            'sql' => 'varchar(50) NOT NULL default \'\''
        ],
        'visits' => [
            'sql' => 'int NOT NULL default 0'
        ],
        'ownerId' => [
            'sql' => 'int NOT NULL default 0'
        ],
        'transferred' => [
            'sql' => 'int NOT NULL default 0'
        ],
    ],
];
