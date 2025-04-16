<?php
/**
 * FolioKit Scheduler
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */
namespace EasyDocLabs\Component\Scheduler;

use EasyDocLabs\Library;

/**
 * Orderable behavior
 *
 * @author Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Scheduler
 */
class DatabaseBehaviorOrderable extends Library\DatabaseBehaviorOrderable
{
    public function _buildQueryWhere($query)
    {
        $query->where('queue = :queue')->bind(['queue' => $this->queue]);
    }

    protected function _beforeInsert(Library\DatabaseContextInterface $context)
    {
        if($this->hasProperty('ordering'))
        {
            if ($this->ordering == -PHP_INT_MAX) {
                $this->ordering = $this->getMinOrdering() - 1;
            }
            elseif($this->ordering <= 0 || $this->ordering == PHP_INT_MAX) {
                $this->ordering = $this->getMaxOrdering() + 1;
            }
        }
    }

    protected function _beforeUpdate(Library\DatabaseContextInterface $context)
    {
        return $this->_beforeInsert($context);
    }

    protected function _afterInsert(Library\DatabaseContextInterface $context)
    {
        $this->reorder();
    }

    protected function _afterUpdate(Library\DatabaseContextInterface $context)
    {
        $this->reorder();
    }

    /**
     * Find the maximum ordering within this parent
     *
     * @return int
     */
    protected function getMinOrdering()
    {
        $table  = $this->getTable();
        $db     = $table->getDriver();

        $query = $this->getObject('database')->getQuery('select')
            ->columns('MIN(ordering)')
            ->table($table->getName());

        $this->_buildQueryWhere($query);

        return (int) $db->select($query, Library\Database::FETCH_FIELD);
    }
}