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

class GutesioDataElementModel extends Model
{
    protected static $strTable = "tl_gutesio_data_element";

    public static function findByUuid(string $uuid)
    {
        return static::findBy('uuid', $uuid, ['return' => 'Model']);
    }

    public static function findByAlias(string $alias)
    {
        return static::findBy('alias', $alias, ['return' => 'Model']);
    }

    public static function findByChildModel(GutesioDataChildModel $childModel)
    {
        $database = Database::getInstance();
        $statement = $database->prepare(
            'SELECT DISTINCT elementId FROM tl_gutesio_data_child_connection WHERE childId = ? LIMIT 1'
        );
        $result = $statement->execute($childModel->uuid)->fetchAssoc();
        return self::findByUuid($result['elementId']);
    }
}