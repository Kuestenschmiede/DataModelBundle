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

$GLOBALS['TL_DCA']['tl_gutesio_data_element_type'] = [
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
        'elementId' => [
            'sql' => 'varchar(50) NOT NULL default \'\''
        ],
        'typeId' => [
            'sql' => 'varchar(50) NOT NULL default \'\''
        ],
        'rank' => [
            'sql' => "int NOT NULL default 0"
        ],
        'importId' => [
            'sql' => "int(20) unsigned NOT NULL default '0'"
        ]
    ],
];

