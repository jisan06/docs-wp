<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class DatabaseBehaviorRelatable extends Library\DatabaseBehaviorAbstract
{
    protected $_property;

    protected $_table;

    protected $_columns;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_property = $config->property;
        $this->_table    = $config->table;
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'priority' => Library\CommandHandlerInterface::PRIORITY_HIGH,
            'property' => 'relations',
            'table'    => 'relations'
        ]);

        parent::_initialize($config);
    }

    protected function _afterInsert(Library\DatabaseContextInterface $context)
    {
        $this->_afterUpdate($context);
    }

    protected function _afterUpdate(Library\DatabaseContextInterface $context)
    {
        $entity             = $context->data;
        $relations          = $this->_getRelations($context);
        $context->relations = [];

        $data = $entity->{$this->_property} ?: [];

        foreach ($data as $relation)
        {
            if (!in_array($relation, $relations)) {
                $context->relations->append([$relation]);
            }
        }

        // Create new relations
        $this->_createRelations($context);

        // Delete old relations
        $context->relations = array_diff($relations, $data);
        $this->_deleteRelations($context);
    }

    protected function _getRelations(Library\DatabaseContextInterface $context)
    {
        $entity  = $context->data;
        $columns = $this->_getColumns($context);

        $query = $this->getObject('lib:database.query.select')->table($this->_getTable()->getName())
                      ->columns($columns['relation'])
                      ->where($columns['id'] . ' = :id')->bind(['id' => $entity->id]);

        return $this->getObject('lib:database.driver.mysqli')->select($query, Library\Database::FETCH_FIELD_LIST);
    }

    protected function _createRelations(Library\DatabaseContextInterface $context)
    {
        if ($relations = Library\ObjectConfig::unbox($context->relations))
        {
            $table   = $this->_getTable();
            $rowset  = $table->createRowset();
            $columns = $this->_getColumns($context);

            foreach ($relations as $relation) {
                $rowset->insert($table->createRow([
                    'data' => [
                        $columns['id']       => $context->data->id,
                        $columns['relation'] => $relation
                    ]
                ]));
            }

            $rowset->save();
        }
    }

    protected function _deleteRelations(Library\DatabaseContextInterface $context)
    {
        if ($relations = Library\ObjectConfig::unbox($context->relations))
        {
            $table   = $this->_getTable();
            $rowset  = $table->createRowset();
            $columns = $this->_getColumns($context);

            foreach ($context->relations as $relation) {
                $rowset->insert($table->createRow([
                    'data' => [
                        $columns['id']       => $context->data->id,
                        $columns['relation'] => $relation
                    ],
                    'status' => Library\Database::STATUS_FETCHED
                ]));
            }

            $rowset->delete();
        }
    }

    protected function _getColumns(Library\DatabaseContextInterface $context)
    {
        if (!$this->_columns)
        {
            $entity = $context->data;

            $columns = [];

            $id_column = $entity->getTable()->getIdentityColumn();

            $table = $this->_getTable();

            foreach ($table->getColumns() as $column) {
                if ($column->name == $id_column) {
                    $columns['id'] = $column->name;
                } else {
                    $columns['relation'] = $column->name;
                }
            }

            $this->_columns = $columns;
        }

        return $this->_columns;
    }

    protected function _getTable()
    {
        if (!$this->_table instanceof Library\DatabaseTableInterface) {
            $this->_table = $this->getObject(sprintf('com:%s.database.table.%s', $this->getIdentifier()->getPackage(), $this->_table));
        }

        return $this->_table;
    }

    public function getMixableMethods($exclude = [])
    {
        $methods = parent::getMixableMethods($exclude);

        $methods[sprintf('get%s', implode('', array_map('ucfirst', explode('_', $this->_property))))] = $this;
        $methods[sprintf('delete%s', implode('', array_map('ucfirst', explode('_', $this->_property))))] = $this;
        $methods[sprintf('relate%s', implode('', array_map('ucfirst', explode('_', $this->_property))))] = $this;

        return $methods;
    }

    public function __call($method, $arguments)
    {
        $methods = [
            'get'    => sprintf('get%s', implode('', array_map('ucfirst', explode('_', $this->_property)))),
            'delete' => sprintf('delete%s', implode('', array_map('ucfirst', explode('_', $this->_property)))),
            'create' => sprintf('relate%s', implode('', array_map('ucfirst', explode('_', $this->_property))))
        ];

        if ($method == $methods['get'])
        {
            $mixer = $this->getMixer();

            if ($mixer instanceof Library\ModelEntityInterface)
            {
                $context = $mixer->getTable()->getContext();

                $context->data = $mixer;

                $result = $this->_getRelations($context);
            }
        }
        elseif (in_array($method, [$methods['create'], $methods['delete']]))
        {
            $context = $this->getTable()->getContext();

            $context->relations = (array) $arguments[0];
            $context->data      = $this->getMixer();

            $result =  strpos($method, 'relate') === 0 ? $this->_createRelations($context) : $this->_deleteRelations($context);
        }
        else $result = parent::__call($method, $arguments);

        return $result;
    }
}