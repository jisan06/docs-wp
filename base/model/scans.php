<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelScans extends Library\ModelDatabase
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->remove('sort')
            ->insert('sort', 'string')
            ->insert('status', 'int')
            ->insert('identifier', 'identifier');
    }

    protected function _buildQueryWhere(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryWhere($query);

        $state = $this->getState();
        
        if ($state->identifier) {
            $query->where('identifier IN :identifier')->bind(['identifier' => (array) $state->identifier]);
        }

        if ($state->status !== null) {
        	$query->where('status IN :status')->bind(['status' => (array) $state->status]);
        }
    }
}
