<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class DatabaseBehaviorRoutable extends Library\DatabaseBehaviorAbstract
{
    public function getRoute()
    {
        $mixer  = $this->getMixer();
        $result = null;

        if (!$mixer->isNew()) {
            $this->getTable()->getCommandChain()->setEnabled(false);
            $query = $this->getObject('lib:database.query.select')
                ->columns(['path'])->table('easydoc_routes')
                ->where('uuid = :uuid')->bind(['uuid' => $mixer->uuid]);
            $result = $this->getTable()->select($query, Library\Database::FETCH_FIELD);
            $this->getTable()->getCommandChain()->setEnabled(true);
        }

        return $result;
    }

    public function generateRoutes($type = null)
    {
        $parameters = ['separator' => '/', 'category' => 'category', 'document' => 'document'];
        $category_paths = $this->getObject('database.query.select');
        $category_paths
            ->bind($parameters)
            ->columns(['path' => 'GROUP_CONCAT(DISTINCT ancestor.slug ORDER BY relation.level DESC SEPARATOR :separator)'])
            ->table(['relation' => 'easydoc_category_relations'])
            ->join(['ancestor' => 'easydoc_categories'],  'ancestor.easydoc_category_id = relation.ancestor_id', 'INNER')
            ->join(['self' => 'easydoc_categories'],  'self.easydoc_category_id = relation.descendant_id', 'LEFT')
            ->group('relation.descendant_id')
            ->order('path');

        $categories = clone $category_paths;
        $categories->columns(['type' => ':category', 'self.uuid']);

        $category_subquery = clone $category_paths;
        $category_subquery->columns(['self.easydoc_category_id']);

        $documents = $this->getObject('database.query.select');
        $documents->columns(['path' => 'CONCAT_WS(:separator, category.path, document.slug)', 'type' => ':document', 'document.uuid'])
            ->bind($parameters)
            ->table(['category' => $category_subquery])
            ->join(['document' => 'easydoc_documents'],  'document.easydoc_category_id = category.easydoc_category_id', 'RIGHT')
            ->order('path');

        $insert = $this->getObject('database.query.insert')
            ->ignore()
            ->table('easydoc_routes')
            ->columns(['path', 'type', 'uuid']);

        $driver = $this->getObject('database.driver.mysqli');

        if (!$type || $type === 'document') {
            $insert_documents = clone $insert;
            $insert_documents->values($documents);

            $driver->insert($insert_documents);
        }

        if (!$type || $type === 'category') {
            $insert_categories = clone $insert;
            $insert_categories->values($categories);

            $driver->insert($insert_categories);
        }
    }

    public function deleteRoutes($type = null)
    {
        $query = $this->getObject('database.query.delete')
            ->table('easydoc_routes');

        if ($type) {
            $query->where('type = :type')->bind(['type' => $type]);
        }

        return $this->getObject('database.driver.mysqli')->delete($query);
    }

    public function regenerateRoutes($type = null)
    {
        $this->deleteRoutes($type);
        $this->generateRoutes($type);
    }

    protected function _afterDelete(Library\DatabaseContextInterface $context)
    {
        $type = $this->getMixer()->getIdentifier()->name;

        $this->regenerateRoutes($type == 'category' ? null : $type);
    }

    protected function _afterInsert(Library\DatabaseContextInterface $context)
    {
        $this->_afterDelete($context);
    }

    protected function _afterUpdate(Library\DatabaseContextInterface $context)
    {
        $this->_afterDelete($context);
    }
}
