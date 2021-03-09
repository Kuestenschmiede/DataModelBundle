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


$GLOBALS['TL_DCA']['tl_gutesio_data_element_extra_zip'] = [
    // Config
    'config' => [
        'sql' => [
            'keys' => [
                'id' => 'primary'
            ],
        ],
    ],
    
    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int unsigned NOT NULL auto_increment',
        ],
        'elementId' => [
            'sql' => 'varchar(50) NOT NULL default \'\''
        ],
        'zip' => [
            'sql' => "varchar(5) NOT NULL default ''"
        ]
    ],
];