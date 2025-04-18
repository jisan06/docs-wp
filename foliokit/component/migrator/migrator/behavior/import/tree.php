<?php
/**
 * @package     Foliokit Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Migrator;

use EasyDocLabs\Library;

class MigratorBehaviorImportTree extends Library\ControllerBehaviorAbstract
{
    /**
     * Tree task handler.
     *
     * Creates a recursive tree structure of a hierarchical data set.
     *
     * @param MigratorContext $context
     *
     * @return array The task output.
     */
    protected function _actionTree(MigratorContext $context)
    {
        $job = $context->getJob();
        $job->append($this->getConfig());

        $job->append(array(
            'identity_column' => 'id',
            'parent_column'   => 'parent_id',
            'root'            => 0,
            'output'          => sprintf('%s_tree', $job->table)
        ));

        $folder = $this->getConfig()->folder;

        if (!is_dir($folder)) {
            mkdir($folder);
        }

        $file = new \SplFileObject(sprintf('%s/%s.txt', $folder, $job->output), 'w');

        $this->_insertTreeLevel($job->root, $file, $job->table, $job->identity_column, $job->parent_column);

        return true;
    }

    /**
     * Recursively inserts tree nodes into a file.
     *
     * @param mixed         $parent_id       The parent identifier of the nodes to be inserted.
     * @param \SplFileObject $file            The file where nodes will be inserted.
     * @param string        $table           The table containing hierarchical data.
     * @param string        $identity_column The table identity column.
     * @param string        $parent_column   The table parent column.
     */
    protected function _insertTreeLevel($parent_id, $file, $table, $identity_column, $parent_column)
    {
        $query = $this->getObject('database')->getQuery('select')
            ->table($table)
            ->columns($identity_column)
            ->where(sprintf('%s = :parent_id', $parent_column))
            ->bind(array('parent_id' => $parent_id));

        $rows = array();

        $adapter = $this->getObject('database');

        foreach ($adapter->select($query, Library\Database::FETCH_FIELD_LIST) as $row) {
            $rows[] = $row;
        }

        foreach ($rows as $row) {
            $file->fwrite($row."\n");
            $this->_insertTreeLevel($row, $file, $table, $identity_column, $parent_column);
        }
    }

}