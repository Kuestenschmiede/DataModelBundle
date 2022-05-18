<?php

$GLOBALS['TL_DCA']['tl_member']['config']['sql']['keys']['cartId'] = 'unique';

$GLOBALS['TL_DCA']['tl_member']['fields']['cartId'] = [
    'sql'                     => "varchar(50) NOT NULL default ''",
];