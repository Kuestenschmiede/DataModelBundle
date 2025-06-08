<?php
$GLOBALS['TL_DCA']['tl_gutesio_data_wishlist'] = [
    'config' => [
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'uuid' => 'index',
                'clientUuid' => 'index'
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
            'sql' => 'int(11) NOT NULL default 0'
        ],
        'clientUuid' => [
            'sql' => 'varchar(50) NOT NULL default \'\''
        ],
        'dataUuid' => [
            'sql' => 'varchar(50) NOT NULL default \'\''
        ],
        'dataTable' => [
            'sql' => 'varchar(75) NOT NULL default ""'
        ],
    ],
];