<?php

$GLOBALS['TL_DCA']['tl_gutesio_data_child_rating'] = [
    'config' => [
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'childId,memberId' => 'unique'
            ],
        ],
    ],
    'fields' => [
        'id' => [
            'sql' => 'int unsigned NOT NULL auto_increment',
        ],
        'childId' => [
            'sql' => 'varchar(50) NOT NULL default \'\''
        ],
        'memberId' => [
            'sql' => "int(10) unsigned NOT NULL default 0"
        ],
        'rating' => [
            'sql' => "int(10) unsigned NOT NULL default 1"
        ]
    ],
];

