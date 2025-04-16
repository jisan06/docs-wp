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
 * Containers Model
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ModelContainers extends Library\ModelDatabase
{
	protected function _buildQueryWhere(Library\DatabaseQueryInterface $query)
	{
		parent::_buildQueryWhere($query);

        $state = $this->getState();

		if ($state->search) {
            $query->where('tbl.title LIKE :search')->bind(['search' =>  '%'.$state->search.'%']);
        }
	}
}
