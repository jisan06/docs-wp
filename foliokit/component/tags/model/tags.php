<?php
/**
 * FolioKit Tags
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Tags;

use EasyDocLabs\Library;

/**
 * Tags Model
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package Koowa\Component\Tags
 */
class ModelTags extends Library\ModelDatabase
{
    /**
     * Constructor.
     *
     * @param Library\ObjectConfig $config Configuration options.
     */
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        // Set the state
        $this->getState()
            ->insert('row', 'cmd')
            ->insert('created_by', 'int');
    }

    /**
     * Initializes the default configuration for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param Library\ObjectConfig $config 	An optional ObjectConfig object with configuration options.
     * @return void
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors' => ['searchable'],
        ]);

        parent::_initialize($config);
    }

    /**
     * Method to get a table object
     *
     * @return Library\DatabaseTableInterface
     */
    final public function getTable()
    {
        if(!($this->_table instanceof Library\DatabaseTableInterface)) {
            $this->_table = $this->getObject('com:tags.database.table.tags', ['name' => $this->_table]);
        }

        return $this->_table;
    }

    /**
     * Method to set a table object attached to the model
     *
     * @param	string	$table The table name
     * @return  $this
     */
    final public function setTable($table)
    {
        $this->_table = $table;
        return $this;
    }

    /**
     * Builds SELECT columns list for the query
     *
     * @param Library\DatabaseQueryInterface $query
     */
    protected function _buildQueryColumns(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryColumns($query);

        $query->columns([
            'count' => 'COUNT( relations.tag_id )'
        ]);

        if($this->getState()->row)
        {
            $query->columns([
                'row' => 'relations.row'
            ]);
        }
    }

    /**
     * Builds GROUP BY clause for the query
     *
     * @param Library\DatabaseQueryInterface $query
     */
    protected function _buildQueryGroup(Library\DatabaseQueryInterface $query)
    {
        $query->group('tbl.slug');
    }

    /**
     * Builds JOINS clauses for the query
     *
     * @param Library\DatabaseQueryInterface $query
     */
    protected function _buildQueryJoins(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryJoins($query);

        if (!$query->isCountQuery())
        {
            $table = $this->getTable()->getName();

            $query->join(['relations' => $table.'_relations'], 'relations.tag_id = tbl.tag_id');
        }
    }

    /**
     * Builds WHERE clause for the query
     *
     * @param Library\DatabaseQueryInterface $query
     */
    protected function _buildQueryWhere(Library\DatabaseQueryInterface $query)
    {
        $state = $this->getState();

        if(!$query->isCountQuery() && $state->row) {
            $query->where('relations.row IN :row')->bind(['row' => (array) $this->getState()->row]);
        }

        if ($state->created_by)
        {
            $query->where('tbl.created_by IN :created_by')
                ->bind(['created_by' => (array) $state->created_by]);
        }

        parent::_buildQueryWhere($query);
    }
}
