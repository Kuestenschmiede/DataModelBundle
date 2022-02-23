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

$GLOBALS['TL_DCA']['tl_gutesio_data_child_product'] = [
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
        'price' => [
            'sql' => "float NULL"
        ],
        'strikePrice' => [
            'sql' => "float NULL"
        ],
        'priceStartingAt' => [
            'sql' => "char(1) NULL"
        ],
        'priceReplacer' => [
            'sql' => "char(9) NULL"
        ],
        'tax' => [
            'sql' => "varchar(7) NOT NULL default 'regular'"
        ],
        'discount' => [
            'sql' => "float(5,2) NULL"
        ],
        'color' => [
            'sql' => "varchar(100) NULL"
        ],
        'size' => [
            'sql' => "varchar(100) NULL"
        ],
        'type' => [
            'sql' => "varchar(32) NOT NULL default ''"
        ],
        'importId' => [
            'sql' => "int(20) unsigned NOT NULL default '0'"
        ],
        'ean' => [
            'sql' => "varchar(32) NOT NULL default ''"
        ],
        'brand' => [
            'sql' => "varchar(100) NOT NULL default ''"
        ],
        'availableAmount' => [
            'sql' => "int NOT NULL default 1"
        ],
        'basePriceUnit' => [
            'sql' => "varchar(10) NOT NULL default ''"
        ],
        'basePriceUnitPerPiece' => [
            'sql' => "float NOT NULL default 0"
        ]
    ],
];

