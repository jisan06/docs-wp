<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Files;

use EasyDocLabs\Library;

/**
 * Relations Attachments Model
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ModelAttachments_relations extends Library\ModelDatabase
{
    protected function _buildQueryWhere(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryWhere($query);

        $state = $this->getState();

        if (!$state->isUnique())
        {
            if ($table = $state->table) {
                $query->where('table = :table');
            }

            if ($row = $state->row) {
                $query->where('row = :row');
            }

            $column = $this->getConfig()->relation_column;

            if ($id = $state->{$column}) {
                $query->where("{$column} = :id");
            }

            $query->bind(['table' => $table, 'row' => $row, 'id' => $id]);
        }
    }
}