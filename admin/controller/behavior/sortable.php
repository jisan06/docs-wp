<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Library;

/**
 * Adds default sorting
 *
 * It is low priority so that persistable kicks in first
 */
class ControllerBehaviorSortable extends Library\ControllerBehaviorAbstract
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'priority' => Library\CommandHandlerAbstract::PRIORITY_LOW
        ]);

        parent::_initialize($config);
    }

    public function isSupported()
    {
        $mixer   = $this->getMixer();
        $request = $mixer->getRequest();

        if ($mixer instanceof Library\ControllerModellable && $mixer->isDispatched() && $request->isGet() && $request->getFormat() === 'html') {
            return true;
        }

        return false;
    }

    protected function _beforeBrowse(Library\ControllerContextInterface $context)
    {
        $query = $context->getRequest()->getQuery();
        $state = $this->getModel()->getState();
        $name  = $this->getMixer()->getIdentifier()->getName();
        $sort  = $direction = null;

        if ($name === 'document') {
            $sort = 'created_on';
            $direction = 'desc';
        } elseif ($name === 'category') {
            $sort = 'ordering';
            $direction = 'asc';
        }

        if (!$query->sort) {
            $query->sort = $sort;
            $state->sort = $sort;
        }

        if (!$query->direction) {
            $query->direction = $direction;
            $state->direction = $direction;
        }

        $state->setProperty('sort', 'default', $sort)
            ->setProperty('direction', 'default', $direction);

    }

}