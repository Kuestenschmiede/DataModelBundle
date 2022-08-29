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

use Contao\Model;

class GutesioDataChildPersonModel extends Model
{
    protected static $strTable = "tl_gutesio_data_child_person";

    public static function findByChildId(string $uuid)
    {
        return static::findBy('childId', $uuid, ['return' => 'Model']);
    }
}