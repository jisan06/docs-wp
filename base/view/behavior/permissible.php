<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ViewBehaviorPermissible extends Library\ViewBehaviorAbstract
{
    /**
     * Optional attributes to be set in the action selector
     *
     * @var array
     */
    static protected $_action_attribs = ['upload_document' => ['data-no-sync' => true]];

    protected function _beforeRender(Library\ViewContextTemplate $context)
    {
        $entity = $this->getModel()->fetch();
        $parent = $entity->getParent();

        $permission = $entity->getPermission();

        if ($parent->isNew()) {
            $parent_permission = $permission->getTable()->createRow();
        } else {
            $parent_permission = $parent->getPermission();
        }

        $parent_inherited = $parent_permission->inherited;

        $usergroups = [];
        $users      = [];
        $inheriting = [];
        $locked     = [];

        $actions = ModelEntityPermission::getActions();

        foreach ($actions as $action)
        {
            $parts = explode('_', $action);

            $section = Library\StringInflector::pluralize($parts[1]);

            $usergroups[$action] = [];
            $users[$action]      = [];

            if ($permission->row == $entity->id)
            {
                $usergroups[$action]['current'] = $permission->data['usergroups'][$action] ?? [];
                $users[$action]['current']      = $permission->data['users'][$action] ?? [];
            }
            else
            {
                $usergroups[$action]['current'] = [];
                $users[$action]['current']      = [];
            }

			$include_locks = strpos($action, 'view') !== 0;
			$can_sync      = isset(self::$_action_attribs[$action]['data-no-sync']) ? !self::$_action_attribs[$action]['data-no-sync'] : true;

            if ($include_locks && $can_sync)
            {
                if (!isset($locked[$section])) $locked[$section] = ['users' => [], 'usergroups' => []];

                $locked[$section]['users']      = array_merge($locked[$section]['users'], $users[$action]['current']);
                $locked[$section]['usergroups'] = array_merge($locked[$section]['usergroups'], $usergroups[$action]['current']);
            }

            if (!$parent->isNew())
            {
                $usergroups[$action]['parent'] = $parent_permission->computed['usergroups'][$action] ?? [];
                $users[$action]['parent']     = $parent_permission->computed['users'][$action] ?? [];
            }
            else
            {
                $default = ModelEntityPermission::getDefaultPermissions();

                $usergroups[$action]['parent'] = isset($default[$action]) ? $default[$action] : [];
                $users[$action]['parent']      = [];
            }

            // Determine which actions have inherited usergroups permissions (excluding default)

            if (isset($parent_inherited['usergroups'][$action])) {
                $inheriting[$action] = true;
            } else {
                $inheriting[$action] = false;
            }
        }

        $context->data->locked = $locked;

		$has_parent = false;
		$view       = $this->getMixer();

		if ($view->isOptionable()) {
				$has_parent = (bool) $this->getOptions()->parent_category;
		}

		$context->data->allowed_usergroups  = $usergroups;
		$context->data->allowed_users       = $users;
		$context->data->inheriting          = $inheriting;
		$context->data->permissions_actions = self::getActions();
		$context->data->category_deselect   = !$has_parent ? $this->getObject('user')->canAddCategory() : false;
    }

    static public function getActions()
    {
        $actions = [];

        foreach (ModelEntityPermission::getActions() as $action)
        {
            if (strpos($action, '_') !== false)
            {
                list($label, $section) = explode('_', $action);

                $section = Library\StringInflector::pluralize($section);

                if (!isset($actions[$section])) $actions[$section] = [];

                $data = ['label' => $label];

                if (isset(self::$_action_attribs[$action])) {
                    $data['attribs'] = self::$_action_attribs[$action];
                }

                $actions[$section][$action] = $data;
            }
        }

        return $actions;
    }
}
