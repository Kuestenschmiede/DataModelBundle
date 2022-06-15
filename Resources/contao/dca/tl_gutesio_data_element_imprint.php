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


$GLOBALS['TL_DCA']['tl_gutesio_data_element_imprint'] = [
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
        'showcaseId' => [
            'sql' => 'varchar(50) NOT NULL default \'\''
        ],
        'addressName' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'addressStreet' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'addressStreetNumber' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'addressZipcode' => [
            'sql' => "varchar(5) NOT NULL default ''"
        ],
        'addressCity' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'addressCountry' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'owner' => [
            'sql' => "text NULL default ''"
        ],
        'contactEmail' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'contactPhone' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'contactFax' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'tradeID' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'registryCourt' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'registerNumber' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'companyForm' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'fundStatement' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'inspectorate' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'standeskammer' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'liquidation' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'responsibleName' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'responsibleStreet' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'responsibleStreetNumber' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'responsibleZipcode' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'responsibleCity' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'importId' => [
            'sql' => "int(20) unsigned NOT NULL default '0'"
        ],
        'imprintType' => [
            'sql' => 'varchar(50) NOT NULL default \'\''
        ],
        'imprintLink' => [
            'sql' => "varchar(128) NOT NULL default ''"
        ],
    ],
];