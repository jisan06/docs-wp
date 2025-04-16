<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelNotifications extends Library\ModelDatabase
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
             ->insert('row', 'cmd')
             ->insert('table', 'cmd')
             ->insert('action', 'cmd')
             ->insert('relations_table', 'cmd')
             ->insert('notifier', 'identifier');
    }

    protected function _buildQueryJoins(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryJoins($query);

        $state = $this->getState();

        // Handle inheritance joins

        if ($state->row && $state->relations_table) {
            $query->join(['relations' => $state->relations_table], 'tbl.row = relations.ancestor_id');
        }
    }

    protected function _buildQueryWhere(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryWhere($query);

        $state = $this->getState();

        if (!empty($state->row))
        {
            if ($state->relations_table) {
                $query->where('(relations.descendant_id IN :row AND (relations.level = :zero OR (tbl.inheritable = :one AND relations.level > :zero)))');
            } else {
                $query->where('row IN :row');
            }

            $query->bind(['row' => (array) $state->row, 'zero' => 0, 'one' => 1]);
        }

        if ($state->table) {
            $query->where('table IN :table')->bind(['table' => (array) $state->table]);
        }

        if ($state->action) {
            $query->where('action IN :action')->bind(['action' => (array) $state->action]);
        }

        if ($notifier = $state->notifier) {
            $query->where('notifier IN :identifier')->bind(['identifier' => (array) $state->notifier]);
        }
    }
}