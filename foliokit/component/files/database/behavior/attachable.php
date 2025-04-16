<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Files;

use EasyDocLabs\Library;

/**
 * Attachable Behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class DatabaseBehaviorAttachable extends Library\DatabaseBehaviorAbstract
{
    /**
     * The identifiable column.
     *
     * @var mixed
     */
    protected $_row_column;

    /**
     * The Attachments model.
     *
     * @var Library\ModelInterface|null
     */
    protected $_model;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_row_column = $config->row_column;

        $aliases = ['com:files.model.attachments' => ['path' => ['model'], 'name' => 'attachments']];

        $manager = $this->getObject('manager');

        foreach ($aliases as $identifier => $alias)
        {
            $alias = array_merge($this->getMixer()->getIdentifier()->toArray(), $alias);

            if (!$manager->getClass($alias, false)) {
                $manager->registerAlias($identifier, $alias);
            }
        }
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['row_column' => 'id']);
        parent::_initialize($config);
    }

    /**
     * After Delete command handler.
     *
     * Removes all the attachments relations and the attachment if not attached to any other resource.
     *
     * @param Library\DatabaseContextInterface $context The context object.
     */
    protected function _afterDelete(Library\DatabaseContextInterface $context)
    {
        $model           = $this->_getModel();
        $relations_model = $model->getRelationsModel();

        $relations = $relations_model->table($this->_getTableName())->row($this->{$this->_row_column})->fetch();

        $column = $relations_model->getConfig()->relation_column;

        foreach ($relations as $relation)
        {
            if ($relation->delete())
            {
                // Check if the attched attachment is still attached to any other resource.
                if (!$relations_model->reset()->{$column}($relation->{$column})->count()) {
                    $model->reset()->{$column}($relation->{$column})->fetch()->delete(); // Delete the attachment
                }
            }
        }
    }

    /**
     * Attachments getter.
     *
     * @return Library\ModelEntityInterface The attachments
     */
    public function getAttachments()
    {
        $model = $this->_getModel();

        if (!$this->isNew()) {
            $attachments = $model->table($this->_getTableName())->row($this->{$this->_row_column})->fetch();
        }  else {
            $attachments = $model->fetch();
        }

        return $attachments;
    }

    /**
     * Resource table name getter.
     *
     * @return string The resource table name.
     */
    protected function _getTableName()
    {
        return $this->_getTable()->getBase();
    }

    /**
     * Resource table getter.
     *
     * @return Library\DatabaseTableInterface The resource table object.
     */
    protected function _getTable()
    {
        $mixer = $this->getMixer();

        if ($mixer instanceof Library\ModelEntityInterface) {
            $table = $mixer->getTable();
        } else {
            $table = $mixer;
        }

        return $table;
    }

    /**
     * Attachments model getter.
     *
     * @return Library\ModelInterface The attachments model object.
     */
    protected function _getModel()
    {
        $identifier = $this->getMixer()->getIdentifier()->toArray();

        $identifier['path'] = ['model'];
        $identifier['name'] = 'attachments';

        return $this->getObject($identifier);
    }

    /**
     * Attachments Relations model getter.
     *
     * @return Library\ModelInterface The attachments relations model object.
     */
    protected function _getRelationsModel()
    {
        $parts = $this->_getModel()->getIdentifier()->toArray();

        $parts['name'] = 'attachments_relations';

        return $this->getObject($parts);
    }
}