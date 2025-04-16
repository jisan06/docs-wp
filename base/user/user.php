<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;
use EasyDocLabs\WP;

class User extends Library\ObjectDecorator implements Library\UserInterface
{
    protected $_data;

	static protected $_categories;

	protected $_groups;

    public function onDecorate($delegate)
    {
        parent::onDecorate($delegate);

        $this->_data = $this->getData();
    }

    public function getData()
    {
        global $duplicate;

        if (is_null($this->_data))
        {
            $data = $this->getObject('com:easydoc.model.users')
                                ->id($this->getId())
                                ->fetch();

            if (!$data->count())
            {
                try
                {
                    $this->_data = $this->getObject('com:easydoc.model.users')->create(['id' => $this->getId()]);
                    $this->_data->save();
                }
                catch (\RuntimeException $exception)
                {
                    // Handle race conditions

                    if (stripos($exception->getMessage(), 'duplicate') && $duplicate !== true)
                    {
                        $duplicate = true; // Avoid an infinite loop, we re-try one more time

                        return $this->getData(); // Run it again
                    }
                    else {
                        throw $exception;
                    }
                }
            }
            else $this->_data = $data->getIterator()->current();
        }

        return $this->_data;
    }

    public function resetData()
    {
        $this->_data = null;
        return $this;
    }

    public function isAdmin()
    {
        return $this->capable('administrator');
    }

    protected function _canExecute($action, Library\ModelEntityInterface $entity = null)
    {
        $method = sprintf('can%s', Library\StringInflector::camelize($action));

        if (!is_null($entity) && !$entity->isPermissible()) throw new \InvalidArgumentException('Entity must implement permissible behavior');

        $entity = $entity ?? $this->getObject('com:easydoc.model.configs')->fetch();

        return $entity->{$method}($this);
    }

    public function __call($method, $arguments)
    {
        if (strpos($method, 'can') === 0)
        {
            $action = Library\StringInflector::underscore(substr($method,3));

            $entity = $arguments[0] ?? null;

            $result = $this->_canExecute($action, $entity);
        }
        else $result = parent::__call($method, $arguments);

        return $result;
    }

    public function capable($capability)
    {
        return WP::user_can($this->getId(), $capability);
    }

    public function getPermissions()
    {
        $user_data = $this->getData();

        if (!$user_data->permissions_map)
        {
            $permissions_data = $this->_generatePermissionsData($this->getId());

			$permissions = $permissions_data['allowed'];

            $driver = $this->_getDriver();

            $driver->execute('START TRANSACTION;');

            $this->_storePermissionsData($permissions);
			$this->_storePermissionsData($permissions_data['forbidden'], false);

            $user_data->setProperty('permissions_map', \EasyDocLabs\WP::wp_json_encode($permissions))
                 ->save();

            $driver->execute('COMMIT;');
        }
        else $permissions = json_decode($user_data->permissions_map, true);

        return $permissions;
    }

    public function clearPermissions()
    {
        $driver = $this->_getDriver();

        $driver->execute('START TRANSACTION;');
        $driver->execute(sprintf('UPDATE #__easydoc_users SET permissions_map = NULL WHERE wp_user_id = %s', $this->getId()));
        $driver->execute(sprintf('DELETE FROM #__easydoc_categories_permissions WHERE wp_user_id = %s', $this->getId()));
        $driver->execute('COMMIT;');

        $this->resetData();

        return $this;
    }

    protected function _storePermissionsData($data, $allowed = true)
    {
        $driver = $this->_getDriver();

        $query = $this->getObject('lib:database.query.insert')
                      ->table('easydoc_categories_permissions')
                      ->ignore();

        $actions_map = array_flip(ModelEntityPermission::getActions(true, true));

        foreach ($data as $action => $categories)
        {
            if (!isset($actions_map[$action])) throw new \RuntimeException(sprintf('Un-supported permission action: %s', $action));

            foreach ($categories as $category)
            {
                $query->values([$category, $actions_map[$action], $this->getId(), $allowed ? 1 : 0]);

                if (count($query->values) >= 1000)
                {
                    $driver->insert($query);
                    $query->values = [];
                }
            }
        }

        if (!empty($query->values)) $driver->insert($query);
    }

    protected function _generatePermissionsData($user_id)
    {
        $categories = self::_getCategories();

		$allowed   = []; // Contains categories for which actions are allowed for the current user (this variable contains the permission map to be stored in the users table)
		$forbidden = []; // Contains categories for which actions are not allowed (forbidden) for the current user while only taking into account permissions that are set or inherited from a parent category (ignoring defaults)
		$paths     = [];
		$inherited = []; // Inherited contains computed permissions excluding defaults

		$groups  = $this->getGroups();
		$default = ['usergroups' => []];

		foreach (ModelEntityPermission::getDefaultPermissions() as $action => $value)
		{
			if (!isset($default['usergroups'][$action])) {
				$default['usergroups'][$action] = $value;
			}
		}

        foreach ($categories as $category)
        {
            $current = !is_null($category['permissions']) ? json_decode($category['permissions'], true) : [];

			if (!empty($paths) && strpos($category['path'], (string) array_key_last($paths)) !== 0)
            {
				$parents = [];

				foreach ($paths as $path => $computed)
				{
					if (strpos($category['path'], (string) $path) !== 0)
					{
						ksort($parents);
						break;
					}

					$parents[$path] = $computed;
				}

				$paths = $parents;
            }

            $computed = end($paths);

            if ($computed)
			{
				$permissions = $this->_inheritPermissions($current, $computed['permissions']);
				$inherited   = $this->_inheritPermissions($current, $computed['inherited']);
            }
			else
			{
				$permissions = $this->_inheritPermissions($current, $default);
				$inherited   = $current;
			}

            foreach ($permissions as $type => $data)
            {
                foreach ($data as $action => $value)
                {
                    $resource   = str_contains($action, '_') ? explode('_', $action)[1] : $action;
                    $action_own = sprintf('%s_own', $action);

                    if (!isset($allowed[$action])) $allowed[$action] = [];
                    if (!isset($allowed[$action_own])) $allowed[$action_own] = [];
					if (!isset($forbidden[$action])) $forbidden[$action] = [];

                    switch ($type)
                    {
                        case 'users':

                            if (!in_array($user_id, $value))
							{
								if (isset($inherited['users'][$action]) && !in_array($user_id, $inherited['users'][$action])) {
									$forbidden[$action][] = $category['id'];
								}
                            }
							else $allowed[$action][] = $category['id'];

                            break;
                        case 'usergroups':

							$result = false;

							// Public check

							if (in_array(ModelEntityUsergroup::FIXED['public']['id'], $value)) {
								$result = true;
							}

							// Registered check

							if (!$result && (!!$user_id && in_array(ModelEntityUsergroup::FIXED['registered']['id'], $value))) {
								$result = true;
							}

							// Usergroups check

							if (!$result && array_intersect($groups, $value)) {
								$result = true;
							}

							if (!$result)
							{
								if (isset($inherited['usergroups'][$action]) && !array_intersect($groups, $inherited['usergroups'][$action])) {
									$forbidden[$action][] = $category['id'];
								}
							}
							else $allowed[$action][] = $category['id'];


							// Keep track of owner permissions

							if (in_array(ModelEntityUsergroup::FIXED['owner']['id'], $value)) {
								$allowed[$action_own][] = $category['id']; // Check here is deferred at runtime as we don't know the entity owner just yet
							}

							break;
                        default:
                            break;
                    }
                }
            }

            $paths[$category['path']] = ['permissions' => $permissions, 'inherited' => $inherited];

			ksort($paths);
        }

		$data = ['allowed' => $allowed, 'forbidden' => $forbidden];

        // Remove duplicates

		foreach ($data as $key => &$value)
		{
			foreach (array_keys($value) as $key) {
				$value[$key] = array_values(array_unique($value[$key]));
			}
		}

		return $data;
    }

    protected static function _getCategories()
    {
        if (!isset(self::$_categories))
        {
			$manager = \Foliokit::getObject('manager');

            $query = $manager->getObject('lib:database.query.select')
                          ->table(['tbl' => 'easydoc_categories'])
                          ->columns([
                              'id'          => 'tbl.easydoc_category_id',
                              'permissions' => 'permissions.data',
                              'level'       => 'COUNT(DISTINCT(crumbs.ancestor_id))',
                              'owner_id'    => 'tbl.created_by',
                              'path'        => 'GROUP_CONCAT(DISTINCT crumbs.ancestor_id ORDER BY crumbs.level DESC SEPARATOR \'/\')'
                          ])
                          ->join(['crumbs' => 'easydoc_category_relations'], 'crumbs.descendant_id = tbl.easydoc_category_id', 'INNER')
                          ->join(['permissions' => 'easydoc_permissions'], 'permissions.row = tbl.easydoc_category_id', 'INNER')
						  ->where('permissions.data IS NOT NULL')
						  ->where('permissions.table = :table')
                          ->group('tbl.easydoc_category_id', 'permissions.easydoc_permission_id')
                          ->order('path', 'ASC')
						  ->bind(['table' => 'easydoc_categories']);

            $result = $manager->getObject('lib:database.driver.mysqli')->select($query, Library\Database::FETCH_ARRAY_LIST);

            if (is_null($result)) {
                $result = [];
            }

            self::$_categories = \SplFixedArray::fromArray($result);
        }

        return self::$_categories;
    }

    protected function _inheritPermissions($current, $parent)
    {
        $diff = [];

        foreach ($parent as $type => $actions)
        {
            if (!isset($diff[$type])) $diff[$type] = [];

            foreach ($actions as $action => $value)
            {
                if (!isset($current[$type][$action])) {
                    $diff[$type][$action] = $parent[$type][$action];
                }
            }
        }

        $computed = array_merge_recursive($diff, $current);

        return $computed;
    }

    public function getId()
    {
        return $this->getDelegate()->getId();
    }

    public function getEmail()
    {
        return $this->getDelegate()->getEmail();
    }

    public function getName()
    {
        return $this->getDelegate()->getName();
    }

    public function getLanguage()
    {
        return $this->getDelegate()->getLanguage();
    }

    public function getTimezone()
    {
        return $this->getDelegate()->getTimezone();
    }

    public function getRoles()
    {
        return $this->getDelegate()->getRoles();
    }

    public function hasRole($role, $strict = false)
    {
        return $this->getDelegate()->hasRole($role, $strict);
    }

    public function getGroups()
    {
		if (!$this->_groups) {
			$this->_groups = $this->getObject('com:easydoc.model.usergroups')->user($this->getId())->fetch()->getIds();
		}

        return $this->_groups;
    }

    public function hasGroup($group, $strict = false)
    {
        return $this->hasGroup($group, $strict);
    }

    public function getPassword()
    {
        return $this->getDelegate()->getPassword();
    }

    public function verifyPassword($password)
    {
        return $this->getDelegate()->verifyPassword($password);
    }

    public function getParameters()
    {
        return $this->getDelegate()->getParameters();
    }

    public function isAuthentic($strict = false)
    {
        return $this->getDelegate()->isAuthentic($strict);
    }

    public function isEnabled()
    {
        return $this->getDelegate()->isEnabled();
    }

    public function isExpired()
    {
        return $this->getDelegate()->isExpired();
    }

    public function setAuthentic()
    {
        return $this->getDelegate()->setAuthentic();
    }

    public function get($name, $default = null)
    {
        return $this->getDelegate()->get($name, $default);
    }

    public function set($name, $value)
    {
        return $this->getDelegate()->set($name, $value);
    }

    public function has($name)
    {
        return $this->getDelegate()->has($name);
    }

    public function remove($name)
    {
        return $this->getDelegate()->remove($name);
    }

    public function toArray()
    {
        return $this->getDelegate()->toArray();
    }

    public function equals(Library\ObjectInterface $object)
    {
        return $this->getDelegate()->equals($object);
    }

    protected function _getDriver()
    {
        return $this->getObject('lib:database.driver.mysqli');
    }
}
