<?php
/**
 * Foliokit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Activities;

use EasyDocLabs\Library;

/**
 * Resources Model
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Activities
 */
class ModelResources extends Library\ModelDatabase
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
             ->insert('uuid', 'string')
             ->insert('package', 'cmd')
             ->insert('name', 'cmd')
             ->insert('resource_id', 'string')
             ->insert('title', 'string')
             ->insert('package_name', 'cmd');
    }

    protected function _buildQueryWhere(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryWhere($query);

        $state = $this->getState();

        if ($uuid = $state->uuid) {
            $query->where('tbl.uuid = :uuid')->bind(['uuid' => $uuid]);
        }

        if ($package = $state->package) {
            $query->where('tbl.package = :package')->bind(['package' => $package]);
        }

        if ($name = $state->name) {
            $query->where('tbl.name = :name')->bind(['name' => $name]);
        }

        if ($resource_id = $state->resource_id) {
            $query->where('tbl.resource_id = :resource_id')->bind(['resource_id' => $resource_id]);
        }

        if ($title = $state->title) {
            $query->where('tbl.title LIKE :title')->bind(['title' => '%' . $title . '%']);
        }

        if ($package_name = (array) $state->package_name)
        {
            $conditions = [];

            $i = 0;

            foreach ($package_name as $value)
            {
                $conditions[] = "(tbl.package = :package{$i} AND tbl.name = :name{$i})";

                list($package, $name) = explode('.', $value);

                $query->bind(["package{$i}" => $package, "name{$i}" => $name]);

                $i++;
            }

            $query->where('(' . implode(' OR ', $conditions) . ')');
        }
    }
}