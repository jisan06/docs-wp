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
 * Permissible database behavior class
 *
 * Handles permissions managment and checks
 *
 * @package EasyDocLabs\EasyDoc
 */
class DatabaseBehaviorPermissible extends DatabaseBehaviorInheritable
{
    /**
     *
     *
     * @var string[]
     */
    protected $_action_map = [
        'upload' => 'upload_document',
        'add'    => 'add_category',
        'edit'   => 'edit_category',
        'delete' => 'delete_category'
    ];

    /**
     * Property containing POST permissions data
     *
     * @var array
     */
    protected $_property;

    /**
     * Loaded permission entities
     *
     * @var array
     */
    static protected $_permission = [];

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_property = $config->property;
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'priority' => Library\CommandHandlerInterface::PRIORITY_HIGH, // Needs to run before access relations get created
            'property' => 'permissions',
            'column'   => 'inherit_permissions'
        ]);

        parent::_initialize($config);
    }

    /**
     * Resets permissions cache
     *
     * @return self
     */
    protected function _reset()
    {
        self::$_permission = [];
    }

    protected function _afterUpdate(Library\DatabaseContextInterface $context)
    {
        $entity = $context->data;

        if ($entity instanceof Library\ModelEntityInterface)
        {
            $data = $context->data->{$this->_property};

            if (!$this->_hasPermission($entity))
            {
                if (!is_null($data) && !$this->_hasRedundantData($context)) {
                    $this->_createPermission($entity, $data); // Create a new permission
                }
            }
            else
            {
                $permission = $entity->getPermission();

                if (!is_null($data))
                {
                    if ($this->_hasNewData($context) && !$this->_hasRedundantData($context))
                    {
                        // Update permission data

                        $permission->data = $data;
                        $permission->save();
                    }
                    elseif ($this->_hasRedundantData($context)) $this->_deletePermission($entity);
                }
                else $this->_deletePermission($entity);
            }
        }

        parent::_afterUpdate($context); // Handle permissions inheritance
    }

    /**
     * Returns the nearest inheritable ancestor
     *
     * @param Library\ModelEntityInterface $entity The entity to look the inheritable ancestor for
     * @return Library\ModelEntityInterface|null The inheritable ancestor, null if no inheritable ancestor was found
     */
    protected function _getInheritableAncestor(Library\ModelEntityInterface $entity)
    {
        // Check if permissions are identical to the next inheritable parent

        $iterator = $entity->getRelatives('ancestors')->getIterator();

        // Sort ancestors on descending path order

        $iterator->uasort(function($a, $b) {
            return strlen($a->path) > strlen($b->path) ? -1 : 1;
        });

        while ($ancestor = $iterator->current())
        {
            if ($this->_isInheritable($ancestor)) break;
            $iterator->next();
        }

        return $ancestor;
    }

    /**
     * Sorts and serializes a permissions array
     *
     * @param array $permissions The permissions array to sort and serialize
     * @return array The sorted and serialized permissions array
     */
    static public function serialize($permissions)
    {
        $permissions = (array) $permissions;

        $callback = function(&$item, $key) use (&$callback)
        {
            if (is_array($item))
            {
                if (is_int(key($item)))
                {
                    asort($item);
                    $item = array_values($item);
                }
                else ksort($item);

                array_walk($item, $callback);
            }
        };

        ksort($permissions);

        array_walk($permissions, $callback);

        return $permissions;
    }

    /**
     * Intersects permission data
     *
     * @param array $perms_1 Permissions data
     * @param array $perms_2 Permissions data
     * @return array An array containing keys that are common to both data sets. The values from the first permission
     *                       data set are kept
     */
    static public function intersect($perms_1, $perms_2)
    {
        $result = [];

        foreach ($perms_1 as $type => $actions)
        {
            if (isset($perms_2[$type]))
            {
                $result[$type] = [];

                foreach ($actions as $name => $values) {
                    if (isset($perms_2[$type][$name])) $result[$type][$name] = $values;
                }
            }
        }

        return $result;
    }

    /**
     * Tells if the context contains new permission POST data
     *
     * @param Library\DatabaseContextInterface $context The context object
     * @return bool True if the context contains new POST permission data, false otherwise
     */
    protected function _hasNewData(Library\DatabaseContextInterface $context)
    {
        $result = false;

        $entity = $context->data;

        $data = $entity->{$this->_property};

        if ($this->_hasPermission($entity))
        {
            if (self::serialize($data) !== self::serialize($entity->getPermission()->data)) {
                $result = true;
            }
        }
        elseif (!empty($data)) $result = true;

        return $result;
    }

    /**
     * Tells if the context contains redundant permission POST data
     *
     * @param Library\DatabaseContextInterface $context The context object
     * @return bool True if the context contains redundant permission POST data, false otherwise
     */
    protected function _hasRedundantData(Library\DatabaseContextInterface $context)
    {
        $result = false;

        $entity = $context->data;

        $data = $entity->{$this->_property};

        $ancestor = $this->_getInheritableAncestor($entity);

        $permission = $this->getPermission(is_null($ancestor));

        $inherited_data = self::intersect($permission->computed, $entity->getPermission()->data);

        if (self::serialize($data) === self::serialize($inherited_data)) {
            $result = true;
        }

        return $result;
    }

    protected function _afterDelete(Library\DatabaseContextInterface $context)
    {
        $entity = $context->data;

        if ($entity instanceof Library\ModelEntityInterface) {
            $this->getObject('com:easydoc.model.permissions')
                 ->table($this->_getTable())
                 ->row($entity->id)
                 ->fetch()
                 ->delete();
        }
    }

    /**
     * Deletes corresponding permission entry from entity
     *
     * @param Library\ModelEntityInterface $entity The entity to delete the permission from
     * @return bool True if deleted, false otherwise
     */
    protected function _deletePermission(Library\ModelEntityInterface $entity)
    {
        $result = $entity->getPermission()->delete();

        $this->_reset();

        return $result;
    }

    /**
     * Creates a permission entry for the entity
     *
     * @param Library\ModelEntityInterface $entity The entity to create the permission for
     * @param                              $data   The permissions data
     */
    protected function _createPermission(Library\ModelEntityInterface $entity, $data)
    {
        $model = $this->getObject('com:easydoc.model.permissions');

        $permission = $model->row($entity->id)->table($this->_getTable())->fetch();

        $row_data = [
            'table' => $this->_getTable(),
            'row'   => $entity->id,
            'data'  => $data
        ];

        if ($permission->isNew()) {
            $permission = $model->getTable()->createRow(['data' => $row_data]);
        } else {
            $permission->setProperties($row_data);
        }

        $permission->save();

        $this->_reset();

        self::$_permission[$entity->getHandle()] = $permission;
    }

    /**
     * Permission entity getter
     *
     * @param bool $new Force a new permission entity return if true
     *
     * @return Library\ModelEntityInterface The permission entity
     */
    public function getPermission($new = false)
    {
        $mixer = $this->getMixer();

        if (!$new && !$mixer->isNew())
        {
            $row = $mixer->id;

            if (!isset(self::$_permission[$row]))
            {
                if ($mixer->{$this->_column}) {
                    $row = $mixer->{$this->_column}; // inheriting permission, use ancestor id
                }

                $permission = $this->getObject('com:easydoc.model.permissions')
                                   ->table($this->_getTable())
                                   ->row($row)
                                   ->fetch();

                if ($permission->isNew()) {
                    $permission = $this->getPermission(true);
                }

                self::$_permission[$row] = $permission;

                if ($row != $mixer->id) {
                    self::$_permission[$mixer->id] = $permission; // Assign permission to the current entity also
                }
            }
            else $permission = self::$_permission[$row];
        }
        else $permission = $this->getObject('com:easydoc.model.permissions')->create();

        return $permission;
    }

    protected function _isRestricted($action)
    {
        $result = false;

        $entity = $this->getMixer();

        if ($entity->isRestrictable()) {
            $result = $entity->isRestrictedAction($action);
        }

        return $result;
    }

    /**
     * Checks if a user can execute a given action based on the current entity permissions
     *
     * @param string                     $action The action name
     * @param Library\UserInterface|null $user   The user object, or null for the current user
     *
     * @return bool True if the action is allowed, false otherwise
     */
    protected function _canExecute($action, User $user = null)
    {
        if (isset($this->_action_map[$action])) $action = $this->_action_map[$action];

        $current = $this->getObject('user');

        if (!is_null($user))
        {
            // Only do a sync if the provided user isn't the current one (otherwise it already took place)

            if ($user->getId() != $current->getId()) {
                $this->getObject('com:easydoc.controller.user')
                     ->sync(['user_id' => $user->getId(), 'hash_check' => true]);
            }
        }
        else  $user = $current;

        // Site admins can do anything

        if (!$user->isAdmin())
        {
			$data   = $this->_getPermissibleData($user);
			$entity = $this->getMixer();
			$result = false;

			if (isset($data->permissible_row))
			{
				$permissions = $data->permissions;

				if (isset($permissions[$action]))
				{
					if (in_array($data->permissible_row, $permissions[$action])) {
						$result = true;
					}
				}

				// Owner check

				$action_own = sprintf('%s_own', $action);

				if (!$result && isset($permissions[$action_own]))
				{
					if (in_array($data->permissible_row, $permissions[$action_own]) && $entity->isCreatable()) {
						$result = $user->getId() == $entity->created_by;
					}
				}
			}
			else
			{
				// Default permission check

				$result = $user->{sprintf('can%s', Library\StringInflector::camelize($action))}();

				if (is_null($result) && $entity->isCreatable()) {
					$result = $entity->created_by == $user->getId();
				}
			}
        }
        else $result = true;

        return $result && !$this->_isRestricted($action);
    }

	protected function _getPermissibleData(Library\UserInterface $user, Library\ModelEntityInterface $entity = null)
    {
        if (is_null($entity)) $entity = $this->getMixer();

        if (!$entity->isNew())
        {
			$data = [
				'permissions'     => $user->getPermissions(),  // The permission map for the user
				'permissible_row' => $entity->permissible_row // The ID of the row with set permissions, null if inheriting from default permissions
			];
        }
		else $data = [];

        return (object) $data;
    }

    /**
     * Checks if the entity permissions are inheritable
     *
     * @param Library\ModelEntityInterface $entity The model entity to check
     * @return bool True if it is inheritable, false otherwise
     */
    protected function _isInheritable(Library\ModelEntityInterface $entity)
    {
        return $entity->getPermission()->row == $entity->id;
    }

    protected function _hasPermission(Library\ModelEntityInterface $entity)
    {
        return !$entity->isNew() && $this->_isInheritable($entity);
    }

    public function getMixableMethods($exclude = array())
    {
        $methods = parent::getMixableMethods($exclude);

        foreach (ModelEntityPermission::getActions() as $action) {
            $methods[sprintf('can%s', Library\StringInflector::camelize($action))] = $this;
        }

        foreach ($this->_action_map as $key => $value) {
            $methods[sprintf('can%s', Library\StringInflector::camelize($key))] = $this;
        }

        return $methods;
    }

    public function __call($method, $arguments)
    {
        $result = null;

		$data = !empty($arguments) ? $arguments[0] : [];

        if (strpos($method, 'can') !== false)
        {
            $parts = Library\StringInflector::explode(str_replace('can', '', $method));

            if (count($parts))
            {
                $action = implode('_', $parts);

                if (isset($this->_action_map[$action])) $action = $this->_action_map[$action];

                if (in_array($action, ModelEntityPermission::getActions()))
                {
                    $user = null;

                    if (isset($data[0]) && $data[0] instanceof User) {
                        $user = $data[0];
                    }

                    $result = $this->_canExecute($action, $user);
                }
            }
        }

        if (!isset($result)) $result = parent::__call($method, $arguments);

        return $result;
    }
}
