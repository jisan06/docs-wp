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

class ModelMimetypes extends Library\ModelDatabase
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->insert('mimetype', 'string')
            ->insert('extension', 'string');
    }

    protected function _buildQueryWhere(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryWhere($query);

        $state = $this->getState();
        
        if ($state->mimetype) {
            $query->where('mimetype IN :mimetype')->bind(['mimetype' => (array) $state->mimetype]);
        }

        if ($state->extension) {
        	$query->where('extension IN :extension')->bind(['extension' => (array) $state->extension]);
        }
    }
}
