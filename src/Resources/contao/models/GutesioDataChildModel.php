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
use gutesio\DataModelBundle\Classes\ChildFullTextContentUpdater;

class GutesioDataChildModel extends Model
{
    protected static $strTable = "tl_gutesio_data_child";

    public static function findByFullTextSearch(array $terms) {
        $updater = new ChildFullTextContentUpdater();
        if ($updater->isFullText() !== true) {
            $updater->addFullText();
        }
        foreach ($terms as $key => $term) {
            $terms[$key] = "$term*";
        }
        $termString = implode(',', $terms);
        $database = Database::getInstance();
        $stmt = $database->prepare(
            'SELECT *, match(fulltextContent) against(\''.$termString.'\' in boolean mode) as relevance '.
            'FROM tl_gutesio_data_child '.
            'where match(fulltextContent) against(\''.$termString.'\' in boolean mode) '.
            'ORDER BY relevance DESC, fulltextContent asc');
        return static::createCollectionFromDbResult($stmt->execute(), static::$strTable);
    }

    public static function findByUuid(string $uuid)
    {
        return static::findBy('uuid', $uuid, ['return' => 'Model']);
    }
}