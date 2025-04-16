<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelBehaviorPermissible extends Library\ModelBehaviorAbstract
{
	protected $_alias;

	protected $_name;

	protected $_access_action = '';

	public function __construct(Library\ObjectConfig $config)
	{
		parent::__construct($config);

		$this->_name          = $config->name;
		$this->_alias         = $config->alias;
		$this->_access_action = $config->access_action;
	}

	protected function _initialize(Library\ObjectConfig $config)
	{
		$config->append(['alias' => 'tbl', 'name' => 'category', 'access_action' => 'view_category']);

		parent::_initialize($config);
	}

	public function onMixin(Library\ObjectMixable $mixer)
    {
        parent::onMixin($mixer);

		$mixer->getState()
			->insert('permission', 'cmd', null, false, [], true) // Filter entities by arbitraty action
			->insert('user', 'int', null, false, [], true)       // The user ID of the user to test permissions against
			->insert('access', 'int', null, false, [], true)     // Convinience state for filtering entities that can be viewed by the user with provided ID
			->insert('strict', 'boolean', true, false, [], true) // If set to true, only entities on which all permissions are allowed will be returned
			->insert('group_access', 'int');                     // Filter entities by groups that can view these entities
    }

    protected function _beforeCount(Library\ModelContextInterface $context)
    {
        $state = $context->getState();

        if ($state->permission && !$state->strict) {
            $context->getQuery()->columns = [
                sprintf('COUNT(DISTINCT %s.%s)', $this->_alias, $this->getMixer()->getTable()->getIdentityColumn())
            ];
        }

        $this->_beforeFetch($context);
    }

    protected function _beforeFetch(Library\ModelContextInterface $context)
    {
		$state = $context->getState();
		$query = $context->getQuery();

		if (is_numeric($state->access))
		{
			$state->permission = $this->_access_action;
			$state->user       = $state->access;
		}

		if ($state->permission && is_numeric($state->user))
		{
			$user = $this->getObject('user.provider')->getUser($state->user);

			if (!$user->isAdmin())
			{
				$context->_user    = $user;
				$context->_actions = (array) Library\ObjectConfig::unbox($state->permission);

				$this->_buildPermissionQuery($context);

				$user->getPermissions();  // Load Permissions at this point as to ensure that these get generated for the user
			}
		}
		elseif ($state->group_access)
		{
			$context->_group_access = $state->group_access;

			 $this->_buildGroupAccessQuery($context);
		}

		if (!$query->isCountQuery())
		{
			$query->join(['easydoc_permissible' => 'easydoc_permissions'], sprintf('COALESCE(%1$s.inherit_permissions, %1$s.easydoc_category_id) = easydoc_permissible.row AND easydoc_permissible.table = :permissions_table', $this->_alias))
				->columns(['permissible_row' => 'easydoc_permissible.row'])
				->bind(['permissions_table' => 'easydoc_categories']);
		}
    }

    public function allowed()
    {
        $result = false;

        $mixer = $this->getMixer();

        if ($mixer->getState()->permission)
        {
            $callback = function(Library\ModelContextInterface $context)
            {
                $context->getQuery()->exists(true);
                $context->mode = Library\Database::FETCH_FIELD;
            };

            $mixer->addCommandCallback('before.fetch', $callback);
            $result = $this->getMixer()->fetch();
            $mixer->removeCommandCallback('before.fetch', $callback);
        }

        return (bool) $result;
    }

    protected function _buildPermissionQuery(Library\ModelContextInterface $context)
    {
        $state = $context->getState();

		$query = $context->getQuery();

		$default_conditions = [];
		$conditions         = [];

		$strict = $state->strict;

		$context->_strict = $strict;

		$default = ModelEntityPermission::getDefaultPermissions();
		$groups  = $context->_user->getGroups();

		$bind_data = ['user' => $context->_user->getId(), 'allowed' => 1];

		$i = 0;

		if (!$strict) {
			$query->join(['permissions' => 'easydoc_categories_permissions'], sprintf('COALESCE(%1$s.inherit_permissions, %1$s.easydoc_category_id) = permissions.easydoc_category_id', $this->_alias));
		}

		$actions_map = array_flip(ModelEntityPermission::getActions(true, true));

		foreach ($context->_actions as $action)
		{
			$own = false;

			if (!isset($actions_map[$action])) throw new \RuntimeException(sprintf('Un-supported permission action: %s', $action));

			$action_id = $actions_map[$action];

			$context->_action = $action;

			$get_default_condition = function($action) use ($i, $context, $strict, $default, $groups)
			{
				$condition = '';

				if (isset($default[$action]))
				{
					$allowed_groups = $default[$action];

					$registered = in_array(ModelEntityUsergroup::FIXED['registered']['id'], $allowed_groups);
					$public     = in_array(ModelEntityUsergroup::FIXED['public']['id'], $allowed_groups);
					$match      = !empty(array_intersect($groups, array_map('intval', $allowed_groups)));
					$owner	    = in_array(ModelEntityUsergroup::FIXED['owner']['id'], $allowed_groups);

					$alias = $strict ? sprintf('permissions_%s', $i) : 'permissions';

					// Public || Registered || Set and match

					if ($public || ($registered && !empty($context->_user->getId())) || $match) {
						$condition = sprintf('ISNULL(%s.easydoc_category_id)', $alias);
					} elseif ($owner) {
						$condition = sprintf('(ISNULL(%s.easydoc_category_id) AND tbl.created_by = :user)', $alias); // Make ownership check if owner is a default option
					}
				}

				return $condition;
			};

			if ($strict)
			{
				$query->join(['permissions_' . $i => 'easydoc_categories_permissions'], sprintf('COALESCE(%1$s.inherit_permissions, %1$s.easydoc_category_id) = permissions_%2$s.easydoc_category_id', $this->_alias, $i));

				$condition = sprintf('((permissions_%1$s.wp_user_id = :user AND permissions_%1$s.allowed = :allowed AND (permissions_%1$s.action = :action_%1$s', $i);

				if ($own = $this->_getOwnerCondition($context)) $condition .= sprintf(sprintf(' OR (%s)', $own), $i);

				$condition .= '))';

				if ($default_condition = $get_default_condition($action)) {
					$condition .= sprintf(' OR %s', $default_condition);
				}

				$condition .= ')';

				$conditions[] = $condition;
			}
			else
			{
				$condition = '(permissions.wp_user_id = :user AND permissions.allowed = :allowed AND (permissions.action = :action_%1$s';

				if ($own = $this->_getOwnerCondition($context)) $condition .= sprintf(' OR (%s)', $own);

				$condition .= '))';

				$conditions[] = sprintf($condition, $i);

				if ($default_condition = $get_default_condition($action)) {
					$default_conditions[] = $default_condition;
				}
			}

			$bind_data['action_' . $i . '_own'] = $actions_map[sprintf('%s_own', $action)];
			$bind_data['action_' . $i]          = $action_id;

			$i++;
		}

		if (!$strict)
		{
			if ($default_conditions)
			{
				array_unique($default_conditions);

				$conditions = array_merge($conditions, $default_conditions);
			}

			$query->distinct()
				->where(sprintf('(%s)', implode(' OR ', $conditions)))
				->bind($bind_data);
		}
		else $query->where(sprintf('(%s)', implode(' AND ', $conditions)))->bind($bind_data);
    }

	protected function _getOwnerCondition(Library\ModelContextInterface $context)
	{
		return sprintf('permissions%s.action = :action_%%1$s_own AND tbl.created_by = :user', $context->_strict ? '_%1$s' : '');
	}

	protected function _buildGroupAccessQuery(Library\ModelContextInterface $context)
	{
		$query = $context->getQuery();

		$query->join(['group_access' => sprintf('easydoc_%s_group_access', $this->_name)],
						sprintf('COALESCE(%1$s.inherit_%2$s_group_access, %1$s.easydoc_category_id) = group_access.easydoc_category_id',
						$this->_alias, $this->_name));

		$default_permissions = ModelEntityPermission::getDefaultPermissions();

		$view_permissions = $default_permissions[sprintf('view_%s', $this->_name)] ?? [];

		$conditions = '(group_access.easydoc_usergroup_id = :group_access';

		// Default (settings) permissions conditions

		$has_default_registered = in_array(ModelEntityUsergroup::FIXED['registered']['id'], $view_permissions);
		$has_default_public     = in_array(ModelEntityUsergroup::FIXED['public']['id'], $view_permissions);

		if ($has_default_public || $has_default_registered || in_array($context->_group_access, (array) $view_permissions)) { // Public || Registered || Set and match
			$conditions .= ' OR ISNULL(group_access.easydoc_category_id))';
		} else {
			$conditions .= ')';
		}

		$query->where($conditions)->bind([
            'group_access' => Library\ObjectConfig::unbox($context->_group_access)
        ]);
	}
}
