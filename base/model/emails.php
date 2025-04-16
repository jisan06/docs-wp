<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelEmails extends Library\ModelDatabase
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);
    
        $state = $this->getState();

        $state->insert('status', 'int');
    }

    protected function _buildQueryWhere(Library\DatabaseQueryInterface $query)
    {
        $state = $this->getState();
    
        if (is_numeric($state->status)) {
            $query->where('status IN :status')->bind(['status' => (array) $state->status]);
        }

        parent::_buildQueryWhere($query);
    }
}