<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

/**
 * Inhertiable database behavior class
 *
 * Handles inheritance synchronisation when creating and updating entities
 *
 * @package EasyDocLabs\EasyDoc
 */
abstract class DatabaseBehaviorInheritable extends Library\DatabaseBehaviorAbstract
{
    /**
     * The entities to update due to inheritance changes
     *
     * var array An array of entities ID to update
     */
    protected $_entities;

    /**
     * The table containing inheritable and inheriting entities
     *
     * @var string The name of the table
     */
    protected $_table;

    /**
     * The column defining entities inheritance
     *
     * var string The column name
     */
    protected $_column;

    /**
     * The ID column of the entities table
     *
     * @var mixed The ID column name
     */
    protected $_id_column;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_column = $config->column;
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(array('column' => 'inherit'));

        parent::_initialize($config);
    }

    protected function _afterInsert(Library\DatabaseContextInterface $context)
    {
        // Same as udpate

        return $this->_afterUpdate($context);
    }

    protected function _afterUpdate(Library\DatabaseContextInterface $context)
    {
        return $this->_syncInheritableData($context->data);
    }

    protected function _syncInheritableData(Library\ModelEntityInterface $entity)
    {
        $this->_entities = [];

        if ($entity instanceof Library\ModelEntityInterface)
        {
            $query = $this->getObject('lib:database.query.update')
                          ->table($this->_getTable());

            if ($this->_isInheritable($entity))
            {
                // Children should inherit from this entity

                if ($this->_isInheriting($entity))
                {
                    // Remove previously set inheritance

                    $entity->getTable()
                           ->getDriver()
                           ->update($this->getObject('lib:database.query.update')
                                         ->table($this->_getTable())
                                         ->values(array(sprintf('%s = NULL', $this->_column)))
                                         ->where(sprintf('%s = :entity', $this->_getPrimaryColumn()))
                                         ->bind(array('entity' => $entity->id)));
                }

                // Set entities that should inherit from current entity

                $this->_setEntities($entity);

                $query->values(array(sprintf('%s = :value', $this->_column)))->bind(array('value' => $entity->id));
            }
            else
            {
                // Current entity should inherit from an ancestor

                $ancestors = $entity->getRelatives('ancestors')->getIterator()->getArrayCopy();

                $ancestor = end($ancestors);

                while ($ancestor)
                {
                    if ($this->_isInheritable($ancestor) || $this->_isInheriting($ancestor))
                    {
                        // Found an entity to inherit from

                        $this->_setEntities($ancestor);

                        $query->values(array(sprintf('%s = :value', $this->_column)))
                              ->bind(array('value' => $this->_isInheriting($ancestor) ? $this->_getInherited($ancestor) : $ancestor->id));

                        break;
                    }

                    $ancestor = prev($ancestors);
                }

                if ($ancestor === false)
                {
                    // Reached root of the hierarchy, process children

                    $this->_entities[] = $entity->id;

                    $this->_setEntities($entity, 0); // zero is added for inherited value comparison to fail

                    $query->values(array(sprintf('%s = NULL', $this->_column)));
                }
            }

            if (!empty($this->_entities))
            {
                $query->where(sprintf('%s IN :entities', $this->_getPrimaryColumn()))->bind(['entities' => $this->_entities]);

                $entity->getTable()->getDriver()->update($query);
            }
        }
    }

    /**
     * Table getter
     *
     * @return string The name of the entities table
     */
    protected function _getTable()
    {
        if (!$this->_table)
        {
            $mixer = $this->getMixer();

            if ($mixer instanceof Library\ModelEntityInterface) {
                $this->_table = $mixer->getTable()->getName();
            }
        }

        return $this->_table;
    }

    /**
     * Primary table column getter
     *
     * @return string The name of the primary column of the mixer table
     */
    protected function _getPrimaryColumn()
    {
        if (!$this->_id_column)
        {
            $mixer = $this->getMixer();

            if ($mixer instanceof Library\ModelEntityInterface)
            {
                $columns = $mixer->getTable()->getPrimaryKey();

                if (isset($columns['id'])) {
                    $this->_id_column = $columns['id']->name;
                }
            }
        }

        return $this->_id_column;
    }

    /**
     * Get inherited column value. This value corresponds to the ID of the entity we are inheriting from
     *
     * @param Library\ModelEntityInterface $entity The model entity to get the inherited value from
     * @return mixed The ID of the inherited entity
     */
    protected function _getInherited(Library\ModelEntityInterface $entity)
    {
        return $entity->{$this->_column};
    }

    /**
     * Inheritance entities setter.
     *
     * It looks up for and set entities that will be inheriting from another entity
     *
     * @param Library\ModelEntityInterface $entity The parent entity to start looking from
     * @param null                         $inherited The ID of the entity inheriting from
     */
    protected function _setEntities(Library\ModelEntityInterface $entity, $inherited = null)
    {
        if (is_null($inherited)) {
            $inherited = $entity->id;
        }

        foreach ($entity->getRelatives('descendants', 1) as $descendant)
        {
            if (!$this->_isInheritable($descendant) && $this->_getInherited($descendant) != $inherited)
            {
                $this->_entities[] = $descendant->id;

                $this->_setEntities($descendant, $inherited);
            }
        }
    }

    /**
     * Checks if the entity is inheritable
     *
     * @param Library\ModelEntityInterface $entity The model entity to check
     * @return bool True if inheritable, false otherwise
     */
    abstract protected function _isInheritable(Library\ModelEntityInterface $entity);

    /**
     * Checks if the entity is inheriting from another entity
     *
     * @param Library\ModelEntityInterface $entity The model entity to check
     * @return bool True if inheriting, false otherwise
     */
    protected function _isInheriting(Library\ModelEntityInterface $entity)
    {
        return !is_null($entity->{$this->_column});
    }
}