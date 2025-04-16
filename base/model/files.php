<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Component\Files;
use EasyDocLabs\Library;

/**
 * Files Model
 *
 * @author      Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package     Nooku_Components
 * @subpackage  Files
 */

class ModelFiles extends ModelNodes
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->remove('folder')->insert('folder', 'com:files.filter.path', null)
            ->remove('name')->insert('name', 'com:files.filter.path', null, true)
            ->insert('extension', 'cmd')
            ->insert('mimetype', 'string')
            ->insert('types'	, 'cmd', '')
            ->insert('count', 'boolean', false)
            ->remove('sort')->insert('sort', 'cmd', 'path');
    }

    protected function _buildQueryColumns(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryColumns($query);

        $query->columns('SUBSTRING_INDEX(tbl.name, ".", -1) AS extension');
        $query->columns('m.mimetype AS mimetype');

        if ($this->getState()->count) {
            $query->columns('fc.count AS count');
        }
    }

    protected function _buildQueryJoins(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryJoins($query);

        $query->join(['m' => 'files_mimetypes'], 'm.extension = SUBSTRING_INDEX(tbl.name, ".", -1)');

        $subquery = $this->getObject('database.query.select');
        $subquery->columns(['storage_path', 'count' => 'COUNT(0)'])
            ->table('easydoc_documents')
            ->where('storage_type = :storage_type')
            ->group('storage_path')
            ->bind(['storage_type' => 'file']);

        if ($this->getState()->count) {
            $query->join(['fc' => $subquery],
                'fc.storage_path = CONCAT_WS("/", NULLIF(tbl.folder, ""), tbl.name)');
        }
    }

    protected function _buildQueryWhere(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryWhere($query);

        $state = $this->getState();

        if ($state->types)
        {
            $types     = (array) $state->types;
            $extension = $state->extension ? (array) $state->extension : [];

            // Image only
            if (in_array('image', $types) && !in_array('file', $types)) {
                $extension = array_merge($extension, Files\ModelEntityFile::$image_extensions);
                $state->extension = $extension;
            }

            if (!in_array('file', $types)) {
                $query->where('1 = 2');
            }
        }

        if ($state->extension)
        {
            $query->where('SUBSTRING_INDEX(tbl.name, ".", -1) IN :extension')
                ->bind(['extension' => (array) $state->extension]);
        }

        if ($state->mimetype)
        {
            $query->where('m.mimetype IN :mimetype')
                ->bind(['mimetype' => (array) $state->mimetype]);
        }
    }
}
