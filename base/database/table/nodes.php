<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class DatabaseTableNodes extends Library\DatabaseTableAbstract
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'base' => 'easydoc_files',
            'name' => 'easydoc_nodes'
        ]);

        parent::_initialize($config);
    }

    public function getSchema()
    {
        $result = parent::getSchema();

        // Unset the primary key from the base table as the view doesn't have any
        if (is_object($result)){
            unset($result->columns['easydoc_file_id']);
        }

        return $result;
    }
}
