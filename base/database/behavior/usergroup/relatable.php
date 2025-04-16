<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class DatabaseBehaviorUsergroupRelatable extends DatabaseBehaviorRelatable
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['property' => 'users', 'table' => 'usergroups_users']);

        parent::_initialize($config);
    }

    /**
     * Deletes categories usergroups relations when a usergroup gets deleted
     *
     * @param Library\DatabaseContextInterface $context
     */
    protected function _afterDelete(Library\DatabaseContextInterface $context)
    {
        $entity = $context->data;

        if (isset($entity->id))
        {
            $tables = ['easydoc_category_group_access', 'easydoc_document_group_access'];
            $driver = $this->getMixer()->getTable()->getDriver();

            foreach ($tables as $table)
            {
                $query = $this->getObject('lib:database.query.delete')
                              ->table($table)
                              ->where('easydoc_usergroup_id = :id')
                              ->bind(['id' => $entity->id]);

                $driver->delete($query);
            }
        }
    }

	protected function _afterUpdate(Library\DatabaseContextInterface $context)
    {
		$context->_synced_users = [];

		parent::_afterUpdate($context);

		// Clear permission data from synced users

		$this->getObject('easydoc.users')->clearPermissions(Library\ObjectConfig::unbox($context->_synced_users));
    }

	protected function _createRelations(Library\DatabaseContextInterface $context)
	{
		parent::_createRelations($context);

		$context->_synced_users->append($context->relations);
	}

	protected function _deleteRelations(Library\DatabaseContextInterface $context)
	{
		parent::_deleteRelations($context);

		$context->_synced_users->append($context->relations);
	}
}
