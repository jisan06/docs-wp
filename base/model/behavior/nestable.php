<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelBehaviorNestable extends Library\ModelBehaviorAbstract
{
    protected function _beforeCount(Library\ModelContextInterface $context)
    {
        $this->_beforeFetch($context);
    }

    public function onMixin(Library\ObjectMixable $mixer)
    {
        parent::onMixin($mixer);

        if ($mixer instanceof Library\ModelDatabase)
        {
            $state = $mixer->getState();

            if (!isset($state->parent_id)) {
                $state->insert('parent_id', 'int');
            }

            if (!isset($state->group_id)) {
                $state->insert('group_id', 'int');
            }

            if (!isset($state->level)) {
                $state->insert('level', 'int');
            }

            if (!isset($state->max_level)) {
                $state->insert('max_level', 'int');
            }

            if (!isset($state->include_self)) {
                $state->insert('include_self', 'boolean', false);
            }

            $state->setProperty('sort', 'default', 'title');

            return true;
        } else {
            return false;
        }
    }

    protected function _beforeFetch(Library\ModelContextInterface $context)
    {
        $state = $context->state;

        if (!$state->isUnique())
        {
            if ($state->sort) {
                $context->query->bind(['sort' => $state->sort]);
            }

            if ($state->direction) {
                $context->query->bind(['direction' => $state->direction]);
            }

            if ($state->include_self) {
                $context->query->bind(['include_self' => $state->include_self]);
            }

            if ($state->parent_id) {
                $context->query->bind(['parent_id' => $state->parent_id]);
            }

            if ($state->group_id) {
                $context->query->bind(['group_id' => $state->group_id]);
            }

            if ($state->level || is_numeric($state->level)) {
                $context->query->bind(['level' => $state->level]);
            }

            if ($state->max_level) {
                $context->query->bind(['max_level' => $state->max_level]);
            }
        }
    }
}
