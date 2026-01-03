<?php
/**
 * This file belongs to gutes.digital and is published exclusively for use
 * in gutes.digital operator or provider pages.

 * @package    gutesio
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design (Matthias Eilers)
 * @link       https://gutes.digital
 */
namespace gutesio\DataModelBundle\Classes;

use Contao\Database;

class ChildFullTextContentUpdater
{
    public function update(int $id = 0)
    {
        $database = Database::getInstance();
        if ($this->isFullText() !== true) {
            $this->addFullText();
        }
        if ($id === 0) {
            $database->prepare(
                'UPDATE tl_gutesio_data_child child
                JOIN tl_gutesio_data_child_type a ON child.typeId = a.uuid 
                LEFT JOIN tl_gutesio_data_child_type b ON a.parentChildTypeId = b.uuid 
                LEFT JOIN tl_gutesio_data_child_type c ON b.parentChildTypeId = c.uuid 
                LEFT JOIN tl_gutesio_data_child_type d ON c.parentChildTypeId = d.uuid 
                LEFT JOIN tl_gutesio_data_child_event ev ON child.uuid = ev.childId 
                LEFT JOIN tl_gutesio_data_element ep ON ep.uuid = ev.locationElementId  
                LEFT JOIN tl_gutesio_data_child_connection cc ON cc.childId = child.uuid
                LEFT JOIN tl_gutesio_data_element el ON el.uuid = cc.elementId
                SET child.fullTextContent =
                CONCAT(
                    IF(child.name is not null, child.name, \'\'),
                    IF(child.description is not null, concat(\' \', child.description), \'\'),
                    IF(a.name is not null, concat(\' \', a.name), \'\'),
                    IF(b.name is not null, concat(\' \', b.name), \'\'),
                    IF(c.name is not null, concat(\' \', c.name), \'\'), 
                    IF(d.name is not null, concat(\' \', d.name), \'\'),
                    IF(ep.name is not null, concat(\' \', ep.name), \'\'),
                    IF(el.name is not null, concat(\' \', el.name), \'\')
                )'
            )->execute();
        } else {
            $database->prepare(
                'UPDATE tl_gutesio_data_child child
                JOIN tl_gutesio_data_child_type a ON child.typeId = a.uuid 
                LEFT JOIN tl_gutesio_data_child_type b ON a.parentChildTypeId = b.uuid 
                LEFT JOIN tl_gutesio_data_child_type c ON b.parentChildTypeId = c.uuid 
                LEFT JOIN tl_gutesio_data_child_type d ON c.parentChildTypeId = d.uuid 
                LEFT JOIN tl_gutesio_data_child_event ev ON child.uuid = ev.childId 
                LEFT JOIN tl_gutesio_data_element ep ON ep.uuid = ev.locationElementId
                LEFT JOIN tl_gutesio_data_child_connection cc ON cc.childId = child.uuid
                LEFT JOIN tl_gutesio_data_element el ON el.uuid = cc.elementId
                SET child.fullTextContent = 
                CONCAT(
                    IF(child.name is not null, child.name, \'\'),
                    IF(child.description is not null, concat(\' \', child.description), \'\'),
                    IF(a.name is not null, concat(\' \', a.name), \'\'),
                    IF(b.name is not null, concat(\' \', b.name), \'\'),
                    IF(c.name is not null, concat(\' \', c.name), \'\'), 
                    IF(d.name is not null, concat(\' \', d.name), \'\'),
                    IF(ep.name is not null, concat(\' \', ep.name), \'\'),
                    IF(el.name is not null, concat(\' \', el.name), \'\')
                ) WHERE child.id = ?'
            )->execute($id);
        }
    }

    public function isFullText()
    {
        $database = Database::getInstance();
        $result = $database->prepare(
            'SHOW INDEX FROM tl_gutesio_data_child WHERE column_name = \'fullTextContent\''
        )->execute()->fetchAssoc();

        return (!empty($result) && $result['Index_type'] === 'FULLTEXT');
    }

    public function addFullText()
    {
        $database = Database::getInstance();
        $database->prepare('ALTER TABLE tl_gutesio_data_child ADD FULLTEXT(fullTextContent)')->execute();
    }
}
