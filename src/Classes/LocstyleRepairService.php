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
     * Optionally uses import data (JSON) as a reference source for more accurate matching.
     * 
     * @param array|null $importData The JSON data from the import
     */
    public function repair(?array $importData = null): void
    {
        $db = Database::getInstance();
        
        // 0. Pre-process import data if provided
        $jsonLocstyleMap = [];
        if ($importData) {
            $jsonLocstyleMap = $this->buildMapFromImportData($importData);
        }

        // Tables that reference tl_c4g_map_locstyles.id
        // Using a map of Table => locstyle_field
        $referencingTables = [
            'tl_gutesio_data_type' => 'locstyle',
            'tl_gutesio_data_element' => 'locstyle',
            'tl_gutesio_data_tag' => 'locstyle',
            'tl_c4g_maps' => 'locstyle',
            'tl_c4g_map_profiles' => 'starboard_locstyles',
            'tl_c4g_routing_configuration' => 'router_from_locstyle',
            'tl_c4g_routing_configuration' => 'router_to_locstyle',
            'tl_c4g_routing_configuration' => 'router_point_locstyle',
            'tl_c4g_routing_configuration' => 'router_interim_locstyle',
            'tl_content' => 'c4g_locstyle',
            'tl_member' => 'c4g_locstyle',
            'tl_calendar_events' => 'c4g_locstyle',
        ];

        // 1. Get all existing locstyles mapped by their name for easy lookup
        $locstylesByName = [];
        $locstylesById = [];
        $locstylesByIcon = [];
        $locstylesByLowerName = [];
        $locstyleResult = $db->execute("SELECT id, name, icon_src, svgSrc FROM tl_c4g_map_locstyles");
        while ($locstyleResult->next()) {
            $lsId = (int)$locstyleResult->id;
            $lsName = $locstyleResult->name;
            if ($lsName) {
                // If there are duplicate names, the last one wins, but usually names should be unique enough for a fallback
                $locstylesByName[$lsName] = $lsId;
                $lsNameLower = mb_strtolower($lsName);
                $locstylesByLowerName[$lsNameLower] = $lsId;
                
                // Add common variants to lower name map
                $locstylesByLowerName['io_' . $lsNameLower] = $lsId;
                $locstylesByLowerName[str_replace([' ', '-'], '_', $lsNameLower)] = $lsId;
                $locstylesByLowerName['io_' . str_replace([' ', '-'], '_', $lsNameLower)] = $lsId;
            }
            $locstylesById[$lsId] = $lsName;
            
            if ($locstyleResult->icon_src) {
                $iconUuid = StringUtil::binToUuid($locstyleResult->icon_src);
                $locstylesByIcon[$iconUuid] = $lsId;
            }
            if ($locstyleResult->svgSrc) {
                $svgUuid = StringUtil::binToUuid($locstyleResult->svgSrc);
                $locstylesByIcon[$svgUuid] = $lsId;
            }
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

            // If we have import data, try repairing based on JSON first (stronger mapping via UUID)
            if (!empty($jsonLocstyleMap[$table])) {
                foreach ($jsonLocstyleMap[$table] as $recordUuid => $lsInfo) {
                    $localRecord = $db->prepare("SELECT id, $field FROM $table WHERE uuid = ?")
                        ->execute($recordUuid)->fetchAssoc();
                    
                    if ($localRecord) {
                        $currentLsId = (int)$localRecord[$field];
                        // Find matching local locstyle for the one in JSON
                        $targetLocstyleId = null;
                        if (!empty($lsInfo['iconUuid'])) {
                            $targetLocstyleId = $locstylesByIcon[$lsInfo['iconUuid']] ?? null;
                        }
                        if (!$targetLocstyleId && !empty($lsInfo['name'])) {
                            $targetLocstyleId = $this->findLocstyleIdByName($lsInfo['name'], $locstylesByName, $locstylesByLowerName);
                        }

                        if ($targetLocstyleId && $targetLocstyleId !== $currentLsId) {
                            $db->prepare("UPDATE $table SET $field = ? WHERE id = ?")
                                ->execute($targetLocstyleId, $localRecord['id']);
                            $repairedCount++;
                        }
                    }
                }
            }

            // Standard fallback repair for remaining orphans
            $orphans = $db->prepare("
                SELECT t.*
                FROM $table t 
                LEFT JOIN tl_c4g_map_locstyles l ON t.$field = l.id 
                WHERE l.id IS NULL AND t.$field > 0
            ")->execute()->fetchAllAssoc();

            foreach ($orphans as $orphan) {
                $recordId = $orphan['id'];
                $recordName = $orphan['name'] ?? '';
                
                // Try matching by name/caption
                $targetLocstyleId = $this->findLocstyleIdByName($recordName, $locstylesByName, $locstylesByLowerName);

                // If not found by name, try matching by icon/svg uuid if available in the record
                // This assumes the referencing table might have an icon_src or svgSrc field too (rare but possible in some custom setups)
                if (!$targetLocstyleId) {
                    if (!empty($orphan['icon_src'])) {
                        $targetLocstyleId = $locstylesByIcon[StringUtil::binToUuid($orphan['icon_src'])] ?? null;
                    }
                    if (!$targetLocstyleId && !empty($orphan['svgSrc'])) {
                        $targetLocstyleId = $locstylesByIcon[StringUtil::binToUuid($orphan['svgSrc'])] ?? null;
                    }
                }

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
                        if (isset($typeEntry['locstyle'])) {
                            $lsId = (int)$typeEntry['locstyle'];
                            // Try matching via JSON mapping first (always enforce if JSON is available)
                            $targetId = null;
                            $caption = $typeEntry['caption'] ?? '';
                            if ($caption && !empty($jsonLocstyleMap['tl_c4g_editor_configuration'][$caption])) {
                                $lsInfo = $jsonLocstyleMap['tl_c4g_editor_configuration'][$caption];
                                if (!empty($lsInfo['iconUuid'])) {
                                    $targetId = $locstylesByIcon[$lsInfo['iconUuid']] ?? null;
                                }
                                if (!$targetId && !empty($lsInfo['name'])) {
                                    $targetId = $this->findLocstyleIdByName($lsInfo['name'], $locstylesByName, $locstylesByLowerName);
                                }
                            }

                            // If no JSON mapping found or failed, only repair if current ID is broken
                            if (!$targetId && !isset($locstylesById[$lsId])) {
                                // Fallback: try to find by caption
                                $targetId = $this->findLocstyleIdByName($caption, $locstylesByName, $locstylesByLowerName);
                                
                                // Try by icon/svg uuid if caption failed
                                if (!$targetId) {
                                    if (!empty($typeEntry['icon_src'])) {
                                        $targetId = $locstylesByIcon[$typeEntry['icon_src']] ?? null;
                                    }
                                    if (!$targetId && !empty($typeEntry['svgSrc'])) {
                                        $targetId = $locstylesByIcon[$typeEntry['svgSrc']] ?? null;
                                    }
                                }
                            }

                            if ($targetId && $targetId !== $lsId) {
                                $typeEntry['locstyle'] = $targetId;
                                $changed = true;
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

        // 3. Repair tl_gutesio_data_element_type (it has no id, only elementId and typeId as keys usually)
        if ($db->tableExists('tl_gutesio_data_element_type')) {
            // This table doesn't have locstyle itself, but references tl_gutesio_data_type
            // which we already repaired. So no direct action needed here unless there are 
            // other hidden locstyle references.
        }

        if ($repairedCount > 0) {
            C4gLogModel::addLogEntry('operator', "Locstyle Repair: $repairedCount Referenzen automatisch korrigiert.");
        }
    }

    /**
     * Tries to find a locstyle ID by a given name/caption using common patterns.
     */
    private function findLocstyleIdByName(string $recordName, array $locstylesByName, array $locstylesByLowerName = []): ?int
    {
        if (!$recordName) {
            return null;
        }

        // Try exact name match first
        if (isset($locstylesByName[$recordName])) {
            return $locstylesByName[$recordName];
        }

        $possibleNames = [
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

        // If still not found, try a case-insensitive search using the pre-built lower map
        if (!empty($locstylesByLowerName)) {
            $lowerRecordName = mb_strtolower($recordName);
            if (isset($locstylesByLowerName[$lowerRecordName])) {
                return $locstylesByLowerName[$lowerRecordName];
            }
            
            $sanitizedRecordName = str_replace([' ', '-'], '_', $lowerRecordName);
            if (isset($locstylesByLowerName[$sanitizedRecordName])) {
                return $locstylesByLowerName[$sanitizedRecordName];
            }
            if (isset($locstylesByLowerName['io_' . $sanitizedRecordName])) {
                return $locstylesByLowerName['io_' . $sanitizedRecordName];
            }
        }

        return null;
    }

    /**
     * Builds a map of Record-UUID => Locstyle-Info from the import JSON.
     */
    private function buildMapFromImportData(array $importData): array
    {
        $map = [];
        
        // 1. Build Locstyle-Lookup from JSON (ID in JSON => Name/Icon in JSON)
        $jsonLocstyles = [];
        if (!empty($importData['tl_c4g_map_locstyles'])) {
            foreach ($importData['tl_c4g_map_locstyles'] as $ls) {
                $lsId = (int)($ls['id'] ?? 0);
                if ($lsId > 0) {
                    $iconUuid = null;
                    if (!empty($ls['svgSrc'])) {
                        $iconUuid = StringUtil::binToUuid(hex2bin($ls['svgSrc']));
                    } elseif (!empty($ls['icon_src'])) {
                        $iconUuid = StringUtil::binToUuid(hex2bin($ls['icon_src']));
                    }
                    
                    $jsonLocstyles[$lsId] = [
                        'name' => $ls['name'] ?? '',
                        'iconUuid' => $iconUuid
                    ];
                }
            }
        }

        // 2. Map referencing tables
        $tables = [
            'tl_gutesio_data_type', 
            'tl_gutesio_data_element', 
            'tl_gutesio_data_tag',
            'tl_c4g_editor_configuration',
            'tl_c4g_maps',
            'tl_c4g_map_profiles',
            'tl_c4g_routing_configuration',
            'tl_content',
            'tl_member',
            'tl_calendar_events'
        ];
        foreach ($tables as $table) {
            if (!empty($importData[$table])) {
                foreach ($importData[$table] as $row) {
                    $rowUuid = $row['uuid'] ?? '';
                    if ($table === 'tl_c4g_editor_configuration') {
                        // Editor configuration uses names/captions within types array
                        $types = StringUtil::deserialize($row['types'] ?? '');
                        if (!is_array($types)) {
                            $types = json_decode($row['types'] ?? '', true);
                        }
                        if (is_array($types)) {
                            foreach ($types as $typeEntry) {
                                $lsId = (int)($typeEntry['locstyle'] ?? 0);
                                $caption = $typeEntry['caption'] ?? '';
                                if ($caption && $lsId > 0 && isset($jsonLocstyles[$lsId])) {
                                    $map[$table][$caption] = $jsonLocstyles[$lsId];
                                }
                            }
                        }
                    } else {
                        // Check multiple potential locstyle fields for some tables
                        $fields = ['locstyle', 'starboard_locstyles', 'router_from_locstyle', 'router_to_locstyle', 'router_point_locstyle', 'router_interim_locstyle', 'c4g_locstyle'];
                        foreach ($fields as $f) {
                            if (isset($row[$f]) && (int)$row[$f] > 0) {
                                $lsId = (int)$row[$f];
                                if ($rowUuid && isset($jsonLocstyles[$lsId])) {
                                    $map[$table][$rowUuid] = $jsonLocstyles[$lsId];
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $map;
    }
}
