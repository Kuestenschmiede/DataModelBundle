<?php
/**
 * This file belongs to gutes.digital and is published exclusively for use
 * in gutes.digital operator or provider pages.
 *
 * @package    gutesio
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design (Matthias Eilers)
 * @link       https://gutes.digital
 */

namespace gutesio\DataModelBundle\Classes;

use Contao\Database;
use Contao\StringUtil;
use con4gis\CoreBundle\Resources\contao\models\C4gLogModel;

/**
 * Service to repair mismatched locstyle IDs in referencing tables.
 */
class LocstyleRepairService
{
    /**
     * Repairs locstyle IDs by matching style names if the referenced ID does not exist.
     */
    public function repair(): void
    {
        $db = Database::getInstance();
        
        // Tables that reference tl_c4g_map_locstyles.id
        // Using a map of Table => locstyle_field
        $referencingTables = [
            'tl_gutesio_data_type' => 'locstyle',
            'tl_gutesio_data_element' => 'locstyle',
            'tl_gutesio_data_tag' => 'locstyle',
        ];

        // 1. Get all existing locstyles mapped by their name for easy lookup
        $locstylesByName = [];
        $locstylesById = [];
        $locstyleResult = $db->execute("SELECT id, name FROM tl_c4g_map_locstyles");
        while ($locstyleResult->next()) {
            if ($locstyleResult->name) {
                // If there are duplicate names, the last one wins, but usually names should be unique enough for a fallback
                $locstylesByName[$locstyleResult->name] = (int)$locstyleResult->id;
            }
            $locstylesById[(int)$locstyleResult->id] = $locstyleResult->name;
        }

        if (empty($locstylesByName)) {
            return;
        }

        $repairedCount = 0;

        // Repair simple FK fields
        foreach ($referencingTables as $table => $field) {
            if (!$db->tableExists($table)) {
                continue;
            }

            $orphans = $db->prepare("
                SELECT t.id, t.name, t.$field as currentLocstyleId 
                FROM $table t 
                LEFT JOIN tl_c4g_map_locstyles l ON t.$field = l.id 
                WHERE l.id IS NULL AND t.$field > 0
            ")->execute()->fetchAllAssoc();

            foreach ($orphans as $orphan) {
                $recordId = $orphan['id'];
                $recordName = $orphan['name'];
                
                $targetLocstyleId = $this->findLocstyleIdByName($recordName, $locstylesByName);

                if ($targetLocstyleId) {
                    $db->prepare("UPDATE $table SET $field = ? WHERE id = ?")
                        ->execute($targetLocstyleId, $recordId);
                    $repairedCount++;
                }
            }
        }

        // Repair serialized locstyle references in editor configuration
        if ($db->tableExists('tl_c4g_editor_configuration')) {
            $configs = $db->execute("SELECT id, types FROM tl_c4g_editor_configuration")->fetchAllAssoc();
            foreach ($configs as $config) {
                if (!$config['types']) {
                    continue;
                }

                $isJson = false;
                $types = StringUtil::deserialize($config['types']);
                if (!is_array($types)) {
                    $types = json_decode($config['types'], true);
                    if (is_array($types)) {
                        $isJson = true;
                    }
                }

                if (is_array($types)) {
                    $changed = false;
                    foreach ($types as &$typeEntry) {
                        if (isset($typeEntry['locstyle']) && $typeEntry['locstyle'] > 0) {
                            $lsId = (int)$typeEntry['locstyle'];
                            if (!isset($locstylesById[$lsId])) {
                                // Broken reference, try to find by caption
                                $caption = $typeEntry['caption'] ?? '';
                                $targetId = $this->findLocstyleIdByName($caption, $locstylesByName);
                                if ($targetId) {
                                    $typeEntry['locstyle'] = $targetId;
                                    $changed = true;
                                }
                            }
                        }
                    }
                    unset($typeEntry);

                    if ($changed) {
                        $newTypes = $isJson ? json_encode($types) : serialize($types);
                        $db->prepare("UPDATE tl_c4g_editor_configuration SET types = ? WHERE id = ?")
                            ->execute($newTypes, $config['id']);
                        $repairedCount++;
                    }
                }
            }
        }

        if ($repairedCount > 0) {
            C4gLogModel::addLogEntry('operator', "Locstyle Repair: $repairedCount Referenzen automatisch korrigiert.");
        }
    }

    /**
     * Tries to find a locstyle ID by a given name/caption using common patterns.
     */
    private function findLocstyleIdByName(string $recordName, array $locstylesByName): ?int
    {
        if (!$recordName) {
            return null;
        }

        $possibleNames = [
            $recordName,
            'io_' . $recordName,
            str_replace(' ', '_', $recordName),
            'io_' . str_replace(' ', '_', $recordName),
            str_replace([' ', '-'], '_', $recordName),
            'io_' . str_replace([' ', '-'], '_', $recordName)
        ];

        foreach ($possibleNames as $name) {
            if (isset($locstylesByName[$name])) {
                return $locstylesByName[$name];
            }
        }

        return null;
    }
}
