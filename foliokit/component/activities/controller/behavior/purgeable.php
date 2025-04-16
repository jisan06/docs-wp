<?php
/**
 * Foliokit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Activities;

use EasyDocLabs\Library;

/**
 * Purgeable Controller Behavior.
 *
 * Adds purge action to the controller.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Activities
 */
class ControllerBehaviorPurgeable extends Library\ControllerBehaviorAbstract
{
    /**
     * Purge action.
     *
     * Deletes activities given a date range.
     *
     * @param Library\ControllerContextInterface $context A command context object.
     * @throws Library\ControllerExceptionActionFailed If the activities cannot be purged.
     * @return Library\ModelEntityInterface
     */
    protected function _actionPurge(Library\ControllerContextInterface $context)
    {
        $model = $this->getModel();
        $state = $model->getState();
        $query = $this->getObject('database')->getQuery('delete');

        $query->table([$model->getTable()->getName()]);

        if ($state->end_date && $state->end_date != '0000-00-00')
        {
            $end_date = $this->getObject('lib:date', ['date' => $state->end_date]);
            $end      = $end_date->format('Y-m-d');

            $query->where('DATE(created_on) <= :end')->bind(['end' => $end]);
        }

        if (!$this->getModel()->getTable()->getAdapter()->execute($query)) {
            throw new Library\ControllerExceptionActionFailed('Delete Action Failed');
        } else {
            $context->status = Library\HttpResponse::NO_CONTENT;
        }
    }
}