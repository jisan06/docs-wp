<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelUsergroups extends Library\ModelDatabase
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

		$this->getState()->insert('user', 'int')
			->insert('hide_admin', 'boolean', false)
			->insert('internal', 'int');
    }

    protected function _buildQueryJoins(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryJoins($query);

        if (is_numeric($this->getState()->user)) {
            $query->join('easydoc_usergroups_users AS rel', 'tbl.easydoc_usergroup_id = rel.easydoc_usergroup_id', 'INNER');
        }
    }

    protected function _buildQueryWhere(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryWhere($query);

        $state = $this->getState();

        if (is_numeric($state->user)) {
            $query->where('rel.wp_user_id IN :user')->bind(['user' => (array) $state->user]);
        }

        if (is_numeric($state->internal)) {
            $query->where('internal = :internal')->bind(['internal' => $state->internal]);
        }

        if ($state->name) {
            $query->where('name = :name')->bind(['name' => $state->name]);
        }

		if ($state->hide_admin) {
			$query->where('NOT (name = :admin AND internal = :one)')->bind(['admin' => 'administrator', 'one' => 1]);
		}
    }
}
