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
 * Attachments Model
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ModelAttachments extends Library\ModelDatabase
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['behaviors' => ['attachable']]);
        parent::_initialize($config);
    }

    protected function _buildQueryColumns(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryColumns($query);

        $query->columns(['tbl.*', 'container_slug' => 'containers.slug']);
    }

    protected function _buildQueryJoins(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryJoins($query);

        $query->join('files_containers AS containers', 'containers.files_container_id = tbl.files_container_id', 'INNER');
    }

    protected function _buildQueryWhere(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryWhere($query);

        $state = $this->getState();

        if (!$state->isUnique())
        {
            if ($container = $state->container) {
                $query->where('tbl.files_container_id = :container');
            }

            if ($name = $state->name)
            {
                $query->where('tbl.name = :name');
            }

            $query->bind(['container' => $container, 'name' => $name]);
        }
    }

    /**
     * Overridden for pushing the container value.
     */
    protected function _actionCreate(Library\ModelContext $context)
    {
        $context->entity->append([
            'container' => $context->state->container,
        ]);

        return parent::_actionCreate($context);
    }
}