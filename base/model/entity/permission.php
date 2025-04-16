<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelEntityPermission extends Library\ModelEntityRow
{
    /**
     * Supported permissions
     *
     * @var array An array of supported permissions
     */
    const ACTIONS = [
        1  => 'view_document',
        2  => 'view_document_own',
        3  => 'view_category',
        4  => 'view_category_own',
        5  => 'upload_document',
        6  => 'upload_document_own',
        7  => 'edit_document',
        8  => 'edit_document_own',
        9  => 'delete_document',
        10 => 'delete_document_own',
        11 => 'download_document',
        12 => 'download_document_own',
        13 => 'add_category',
        14 => 'add_category_own',
        15 => 'edit_category',
        16 => 'edit_category_own',
        17 => 'delete_category',
        18 => 'delete_category_own'
    ];

    protected static $_internal_usergroups;

	protected static $_default;

    static public function getActions($ids = false, $own = false)
    {
        $actions = self::ACTIONS;

        if ($own === false) {
            $actions = array_filter($actions, function($value) {
                return !str_ends_with($value, '_own');
            });
        }

        return $ids === true ? $actions : array_values($actions);
    }

	static public function getActionId($name)
	{
		$result = false;

		$actions = array_flip(SELF::ACTIONS);

		if (isset($actions[$name])) $result = $actions[$name];

		return $result;
	}

    public function getProperty($name)
    {
        if ($name == 'data')
        {
            if ($data = Library\ObjectArray::offsetGet('data')) {
                $value = json_decode($data, true);
            } else {
                $value = [];
            }
        }
        else $value = parent::getProperty($name);

        return $value;
    }

    public function setPropertyData($value)
    {
        if (!is_string($value)) {
            $value = \EasyDocLabs\WP::wp_json_encode($value);
        }

        return $value;
    }

    public function getPropertyInherited()
    {
        $inherited = [];

        if (!$this->isNew())
        {
            $query = $this->getObject('lib:database.query.select');

            $query->columns('perms.data')
                  ->table(['perms' => 'easydoc_permissions'])
                  ->join('easydoc_category_relations AS rels', 'perms.row = rels.ancestor_id')
                  ->where('rels.descendant_id = :row')
                  ->order('rels.level', 'DESC')
                  ->bind(['row' => $this->row]);

            $data = $this->getTable()->getDriver()->select($query, Library\Database::FETCH_FIELD_LIST);

            foreach ($data as $permissions)
            {
                $permissions = json_decode($permissions, true);

                foreach ($permissions as $type => $actions)
                {
                    if (!isset($inherited[$type])) $inherited[$type] = [];

                    foreach ($actions as $action => $value) {
                        $inherited[$type][$action] = $permissions[$type][$action];
                    }
                }
            }
        }

        return $inherited;
    }

    public function getPropertyComputed()
    {
        if (!$this->isNew())
        {
            $computed = $this->inherited;

            $default = self::getDefaultPermissions();

            foreach ($default as $action => $groups)
            {
                if (empty($computed['usergroups'][$action])) {
                    $computed['usergroups'][$action] = $groups;
                }
            }
        }
        else
        {
            $default = self::getDefaultPermissions();

            if (!empty($default)) {
                $computed = ['usergroups' => $default];
            } else {
                $computed = [];
            }
        }

        return $computed;
    }

    static public function getDefaultPermissions()
    {
		if (!self::$_default)
		{
			$permissions = \Foliokit::getObject('manager')->getObject('com:easydoc.model.configs')->fetch()->permissions;

			self::$_default = array_filter($permissions, function($key) {
				return in_array($key, self::ACTIONS);
			}, ARRAY_FILTER_USE_KEY);
		}

		return self::$_default;

    }
}
