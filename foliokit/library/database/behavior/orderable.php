<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Library;

/**
 * Orderable Database Behavior
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Database\Behavior
 */
class DatabaseBehaviorOrderable extends DatabaseBehaviorAbstract
{
    /**
     * Check if the behavior is supported
     *
     * Behavior requires a 'ordering' row property
     *
     * @return  boolean  True on success, false otherwise
     */
    public function isSupported()
    {
        $table = $this->getMixer();

        //Only check if we are connected with a table object, otherwise just return true.
        if($table instanceof DatabaseTableInterface)
        {
            if(!$table->hasColumn('ordering'))  {
                return false;
            }
        }

        return true;
    }

    /**
     * Override to add a custom WHERE clause
     *
     * <code>
     * 	   $query->where('category_id = :category_id')->bind(array('category_id' => $this->id));
     * </code>
     *
     * @param   DatabaseQuerySelect $query
     * @return  void
     *
     * @throws \InvalidArgumentException
     */
    public function _buildQueryWhere($query)
    {
        if(!$query instanceof DatabaseQuerySelect && !$query instanceof DatabaseQueryUpdate)
        {
            throw new \InvalidArgumentException(
                'Query must be an instance of DatabaseQuerySelect or DatabaseQueryUpdate'
            );
        }
    }

    /**
     * Move the row up or down in the ordering
     *
     * Requires an 'ordering' column
     *
     * @param   integer $change Amount to move up or down
     * @return  DatabaseRowAbstract
     */
    public function order($change)
    {
        //force to integer
        settype($change, 'int');

        if($change !== 0)
        {
            $old = (int) $this->ordering;
            $new = $this->ordering + $change;
            $new = $new <= 0 ? 1 : $new;

            $table = $this->getTable();
            $query = $this->getObject('lib:database.query.update', ['driver' => $table->getDriver()])
                ->table($table->getBase());

            //Build the where query
            $this->_buildQueryWhere($query);

            if($change < 0)
            {
                $query->values('ordering = ordering + 1')
                    ->where('ordering >= :new')
                    ->where('ordering <  :old')
                    ->bind(array('new' => $new, 'old' => $old));
            }
            else
            {
                $query->values('ordering = ordering - 1')
                ->where('ordering <=  :new')
                ->where('ordering > :old')
                ->bind(array('new' => $new, 'old' => $old));
            }

            $table->getDriver()->update($query);

            $this->ordering = $new;
            $this->save();
            $this->reorder();
        }

        return $this->getMixer();
    }

     /**
     * Resets the order of all rows
     *
     * Resetting starts at $base to allow creating space in sequence for later record insertion.
     *
     * @param   integer $base Order at which to start resetting.
     * @return  DatabaseBehaviorOrderable
     */
    public function reorder($base = 0)
    {
        //force to integer
        settype($base, 'int');

        $table  = $this->getTable();
        $db     = $table->getDriver();
        $db->execute('SET @order = '.$base);

        $query = $this->getObject('lib:database.query.update', ['driver' => $db])
            ->table($table->getBase())
            ->values('ordering = (@order := @order + 1)')
            ->order('ordering', 'ASC');

        //Build the where query
        $this->_buildQueryWhere($query);

        if ($base) {
            $query->where('ordering >= :ordering')->bind(array(':ordering' => $base));
        }

        $db->update($query);

        return $this;
    }

    /**
     * Find the maximum ordering within this parent
     *
     * @return int
     */
    protected function getMaxOrdering()
    {
        $table  = $this->getTable();
        $db     = $table->getDriver();

        $query = $db->getQuery('select')
            ->columns('MAX(ordering)')
            ->table($table->getName());

        $this->_buildQueryWhere($query);

        return (int) $db->select($query, Database::FETCH_FIELD);
    }

    /**
     * Saves the row to the database.
     *
     * This performs an intelligent insert/update and reloads the properties with fresh data from the table on success.
     *
     * @param   DatabaseContextInterface $context
     * @return DatabaseRowAbstract
     */
    protected function _beforeInsert(DatabaseContextInterface $context)
    {
        if($this->hasProperty('ordering'))
        {
            if($this->ordering <= 0) {
                $this->ordering = $this->getMaxOrdering() + 1;
            } else {
                $this->reorder($this->ordering);
            }
        }
    }

    /**
     * Changes the rows ordering if the virtual order field is set. Order is relative to the row's current position.
     *
     * @param   DatabaseContextInterface $context
     */
    protected function _beforeUpdate(DatabaseContextInterface $context)
    {
        if(isset($this->order) && $this->hasProperty('ordering')) {
            $this->order($this->order);
        }
    }

    /**
     * Clean up the ordering after an item was deleted
     *
     * @param   DatabaseContextInterface $context
     */
    protected function _afterDelete(DatabaseContextInterface $context)
    {
        $this->reorder();
    }
}
