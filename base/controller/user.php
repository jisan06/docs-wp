<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;
use EasyDocLabs\WP;

class ControllerUser extends Base\ControllerUser
{
    protected function _actionSync(Library\ControllerContext $context)
    {
        global $wpdb;

        $roles = WP::wp_roles()->roles;

        $model = $this->getObject('com:easydoc.model.usergroups')->internal(1);

        $driver = $model->getTable()->getDriver();

        $config = $context->param;

        $user_id = $config->user_id ?? null;

        if ($config->reset)
        {
            if ($user_id) {
                $query = sprintf('DELETE rels FROM #__easydoc_usergroups_users AS rels INNER JOIN #__easydoc_usergroups AS usergroups ON usergroups.easydoc_usergroup_id = rels.easydoc_usergroup_id WHERE usergroups.internal = 1 AND rels.wp_user_id = %s', $user_id);
            } else {
                $query = 'DELETE rels FROM #__easydoc_usergroups_users AS rels INNER JOIN #__easydoc_usergroups AS usergroups ON usergroups.easydoc_usergroup_id = rels.easydoc_usergroup_id WHERE usergroups.internal = 1';
            }

            $driver->execute($query);

            // Cleanup orphan internal groups that might not have been deleted

            foreach ($model->fetch() as $group) {
                if (!isset($roles[$group->name])) $group->delete();
            }

            $model->reset();
        }

        foreach ($roles as $name => $role)
        {
            $group = $model->name($name)->fetch();

            if ($group->isNew())
            {
                $group = $model->create(array('name' => $name, 'internal' => 1));
                $group->save();
            }

            $query = sprintf('INSERT IGNORE INTO #__easydoc_usergroups_users (easydoc_usergroup_id, wp_user_id)
                  SELECT %s, users.ID FROM %susermeta AS usermeta
                  INNER JOIN %susers AS users ON usermeta.user_id = users.ID WHERE usermeta.meta_key = \'%scapabilities\'
                  AND usermeta.meta_value LIKE \'%%%s%%\'', $group->id, $wpdb->base_prefix, $wpdb->base_prefix, $wpdb->prefix, $name);

            if ($user_id) {
                $query .= sprintf(' AND usermeta.user_id = %s', $user_id);
            }

            $driver->execute($query);
        }

        $query = sprintf('INSERT INTO #__easydoc_users (wp_user_id, permissions_map, roles_hash)
            SELECT usermeta.user_id, NULL, MD5(usermeta.meta_value) FROM %susermeta AS usermeta
            WHERE usermeta.meta_key = \'%scapabilities\'', $wpdb->base_prefix, $wpdb->prefix);

        if ($user_id) $query .= sprintf(' AND usermeta.user_id = %s', $user_id);

        $query .= ' ON DUPLICATE KEY UPDATE roles_hash = MD5(usermeta.meta_value)';

        $driver->execute($query);

        // Permissions should be re-generated at this point

        if ($user_id) {
            $this->getObject('user.provider')->getUser($user_id)->clearPermissions();
        } else {
            $this->getObject('easydoc.users')->clearPermissions();
        }

        if ($config->_action == 'sync') $context->getResponse()->send(); // Send response right away for POSTed forms
    }

    protected function _beforeSync(Library\ControllerContextInterface $context)
    {
        $sync = true;

        $config = $context->param;

        $request = $context->getRequest();

        if ($request->isPost() && $request->getData()->_action == 'sync') {
            $context->param = $request->getData()->toArray();
        }

        if ($config->hash_check)
        {
            $sync = (bool) !$this->_isRolesHashValid($context);

            if ($sync) {
                $config->reset = true; // Out of sync, reset current groups users relations
            }
        }

        return $sync;
    }

    protected function _isRolesHashValid(Library\ControllerContextInterface $context)
    {
        global $wpdb;

        $driver = $this->getModel()->getTable()->getDriver();

        $config = $context->param;

        $user_id = $config->user_id;

        if ($user_id !== 0)
        {
            $query =  sprintf('SELECT %%s FROM %seasydoc_users AS data INNER JOIN %susermeta AS usermeta ON data.wp_user_id = usermeta.user_id WHERE usermeta.meta_key = \'%%scapabilities\'', $wpdb->prefix, $wpdb->base_prefix);

            if ($user_id)
            {

                $query .= ' AND MD5(usermeta.meta_value) = data.roles_hash AND usermeta.user_id = %s';
                
                $query = sprintf($query, 'COUNT(*) = 1', $wpdb->prefix, $user_id);
            }
            else
            {
                $query .= ' AND MD5(usermeta.meta_value) <> data.roles_hash';
                
                $query = sprintf($query, 'COUNT(*) > 1', $wpdb->prefix);
            }

            $query = $this->getObject('com:easydoc.database.query.select', ['query' => $query]);

            $result = $driver->select($query, Library\Database::FETCH_FIELD);
        }
        else $result = true; // There's no hash check against guest users, assume everything is alright

        return (bool) $result;
    }
}