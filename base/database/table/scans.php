<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class DatabaseTableScans extends Library\DatabaseTableAbstract
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors' => [
                'lib:database.behavior.creatable',
                'lib:database.behavior.modifiable',
                'lib:database.behavior.parameterizable'
            ],
            'filters'   => [
                'parameters' => ['json'],
            ]
        ]);

        parent::_initialize($config);
    }
}
