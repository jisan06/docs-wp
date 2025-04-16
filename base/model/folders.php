<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

/**
 * Files Model
 *
 * @author      Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package     Nooku_Components
 * @subpackage  Files
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelFolders extends ModelNodes
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->remove('folder')->insert('folder', 'com:files.filter.path', null)
            ->remove('name')->insert('name', 'com:files.filter.path', null, true)
            ->remove('sort')->insert('sort', 'cmd', 'path')
            ->insert('tree', 'boolean', false);
    }

    protected function _buildQueryWhere(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryWhere($query);

        $state = $this->getState();

        if ($state->types)
        {
            $types     = (array) $state->types;

            if (!in_array('folder', $types)) {
                $query->where('1 = 2');
            }
        }

    }
}
