<?php
/**
 * This file is part of con4gis,
 * the gis-kit for Contao CMS.
 *
 * @package   	con4gis
 * @version    7
 * @author  	    con4gis contributors (see "authors.txt")
 * @license 	    LGPL-3.0-or-later
 * @copyright 	Küstenschmiede GmbH Software & Design
 * @link              https://www.con4gis.org
 *
 */

namespace gutesio\DataModelBundle\Resources\contao\models;

use Contao\Database;
use Contao\Model;

class GutesioDataElementOperatorContactModel extends Model
{
    protected static $strTable = "tl_gutesio_data_element_operator_contact";

    public static function insertOrUpdate(
        int $lastContactAt,
        string $elementId,
        string $operatorId,
        string $initiator = 'o'
    ) {
        if ($lastContactAt < 0) {
            $lastContactAt = 0;
        }
        $database = Database::getInstance();
        $row = $database->prepare(
            'SELECT id FROM '.static::$strTable.' WHERE elementId = ? AND operatorId = ? AND initiator = ?'
        )->execute($elementId, $operatorId, $initiator);
        if ($row->numRows > 0) {
            $database->prepare(
                'UPDATE '.static::$strTable.' SET lastContactAt = ? '.
                'WHERE elementId = ? AND operatorId = ? AND initiator = ?'
            )->execute($lastContactAt, $elementId, $operatorId, $initiator);
        } else {
            $database->prepare(
                'INSERT INTO '.static::$strTable.' (lastContactAt, elementId, operatorId, initiator) '.
                'VALUES (?,?,?,?)'
            )->execute($lastContactAt, $elementId, $operatorId, $initiator);
        }
    }
}