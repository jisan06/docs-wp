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

class ModelEntityUsergroup extends Library\ModelEntityRow
{
    /**
     * Fixed usergroups
     *
     * These are hardcoded usergroups. Supported groups are public (which means everyone can execute
     * the action), registered (which includes all registered users), owner (owner can execute actions on entities they own)
	 * and admins (which locks group permissions to administrators only)
     *
     * @var array An array containing fixed usergroups
     */
	const FIXED = [
		'public'      => ['id' => '-1', 'exclusive' => true, 'syncable' => true],
		'registered'  => ['id' => '-2', 'exclusive' => true, 'syncable' => true],
		'owner'       => ['id' => '-3', 'exclusive' => false, 'syncable' => true],
		'admins only' => ['id' => '-4', 'exclusive' => true, 'syncable' => false]
	];

    public function getUsers($column = null)
    {
        if (!$this->isNew())
        {
            $users = $this->getObject('com:base.model.users')
                          ->addBehavior('com:easydoc.model.behavior.groupable')
                          ->group($this->id)
                          ->fetch();

            if (isset($column) && isset($users->{$column}))
            {
                $values = [];

                if ($users->count())
                {
                    foreach ($users as $user) {
                        $values[] = $user->{$column};
                    }
                }

                $users = $values;
            }
        }
        else $users = null;

        return $users;
    }

    public function save()
    {
        // Check if a group with the same name exists

        $group = $this->getObject('com:easydoc.model.usergroups')->name($this->name)->internal($this->internal)->fetch();

        if (!$group->isNew() && $this->id != $group->id)
        {
            $this->setStatus(Library\Database::STATUS_FAILED);
            $this->setStatusMessage($this->getObject('translator')->translate('A group with the same name already exists'));

            $result = false;
        }
        else $result = parent::save();
        
        
        return $result;
    }

    public function hasUser($user_id)
    {
        $result = false;

        if (!$this->isNew()) {
            $result = (bool) $this->getObject('com:easydoc.model.usergroups')->id($this->id)->user($user_id)->count();
        }

        return $result;
    }

    public function getPropertyDisplayname()
    {
        if ($this->isInternal()) {
            $result = WP::wp_roles()->get_names()[$this->name];
        } else {
            $result = parent::getProperty('name');
        }

        return $result;
    }

    public static function getFixed($filter = [])
    {
        $result = [];

        foreach (self::FIXED as $name => $values)
        {
            if (!empty($filter))
            {
                foreach ($filter as $key => $value)
                {
                    if (isset($values[$key]) && $values[$key] == $value) {
                        $result[$values['id']] = $name;
                    }
                }
            }
            else $result[$values['id']] = $name;
        }

        return $result;
    }

    public function getRole()
    {
        $role = null;

        if (!$this->isNew() && $this->internal)
        {
            $roles = WP::wp_roles();

            $role = $roles->get_role($this->name);
        }

        return $role;
    }

    public function isInternal()
    {
        return !$this->isNew() && ($this->internal == 1);
    }
}
