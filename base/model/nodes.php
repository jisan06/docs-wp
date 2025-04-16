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

class ModelNodes extends Library\ModelDatabase
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->remove('folder')->insert('folder', 'com:files.filter.path', null)
            ->remove('name')->insert('name', 'com:files.filter.path', null, true)
            ->remove('sort')->insert('sort', 'string', 'type DESC, path')
            ->insert('tree', 'boolean', false);
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors' => ['searchable' => ['columns' => 'name']]
        ]);

        parent::_initialize($config);
    }

    public static function getUnionQuery()
    {
        $union          = \Foliokit::getObject('database.query.union');
        $select_files   = \Foliokit::getObject('database.query.select');
        $select_folders = \Foliokit::getObject('database.query.select');

        $columns = ['*', 'path' => 'CONCAT_WS("/", NULLIF(folder, ""), name)', 'type' => ":type"];

        $select_files->table('easydoc_files')->columns($columns)->bind(['type' => 'file']);
        $select_folders->table('easydoc_folders')->columns($columns)->bind(['type' => 'folder']);

        return $union->union($select_files)->union($select_folders);
    }

    protected function _buildQuery(Library\ModelContextDatabase $context)
    {
        parent::_buildQuery($context);

        // Use the UNION query in lieu of the (imaginary) table
        if ($this->getTable()->getName() === 'easydoc_nodes') {
            $context->query->table = [];
            $context->query->table(['tbl' => static::getUnionQuery()]);
        }
    }

    protected function _buildQueryColumns(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryColumns($query);

        // easydoc_nodes has these as a calculated column in the UNION query
        if ($this->getTable()->getName() !== 'easydoc_nodes') {
            $query->columns(['path' => 'CONCAT_WS("/", NULLIF(tbl.folder, ""), tbl.name)']);
        }
    }

    protected function _buildQueryWhere(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryWhere($query);

        $state = $this->getState();

        if (!$state->tree)
        {
            $folder = $state->folder ? (array)$state->folder : [''];

            $query->where('folder IN :folder')
                ->bind(['folder' => $folder]);
        }
    }
}
