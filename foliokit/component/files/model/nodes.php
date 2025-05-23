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
 * Nodes Model
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ModelNodes extends Library\ModelAbstract
{
    /**
     * A container object
     *
     * @var ModelEntityContainer
     */
    protected $_container;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->insert('limit'     , 'int')
            ->insert('offset'    , 'int')
            ->insert('sort'      , 'cmd')
            ->insert('direction' , 'word', 'asc')
            ->insert('search'    , 'string')

            ->insert('container', 'com:files.filter.container', null)
            ->insert('folder'	, 'com:files.filter.path', '')
            ->insert('name'		, 'com:files.filter.path', '', true)

            ->insert('types'	, 'cmd', '')
            // used in modal windows
            ->insert('editor'   , 'string', '')
            // used to pass options to the JS application in HMVC, internal
            ->insert('config'   , 'raw', '', false, [], true);

        $this->addCommandCallback('after.reset', '_afterReset');
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'state'        => 'com:files.model.state.nodes',
            'identity_key' => 'name',
            'behaviors'    => ['paginatable', 'com:files.model.behavior.nodes.thumbnailable'],
        ]);

        parent::_initialize($config);
    }

    protected function _actionCreate(Library\ModelContext $context)
    {
        $state  = $context->getState();
        $entity = $context->properties;

        if ($uri = $state->uri) {
            $entity->append(['uri' => $state->uri]);
        } else {
            $entity->append([
                'folder'    => $state->folder,
                'name'      => $state->name,
                'container' => $state->container
            ]);
        }

        return parent::_actionCreate($context);
    }

    protected function _actionFetch(Library\ModelContext $context)
    {
        $state = $context->state;

        $type = !empty($state->types) ? (array)$state->types : [];

        $list = $this->getObject('com:files.model.entity.nodes');

        // Special case for limit=0. We set it to -1 so loop goes on till end since limit is a negative value
        $limit_left  = $state->limit ? $state->limit : -1;
        $offset_left = $state->offset;
        $total       = 0;

        if (empty($type) || in_array('folder', $type))
        {
            $folders = $this->getObject('com:files.model.folders')->setState($state->getValues());

            foreach ($folders->fetch() as $folder)
            {
                if (!$limit_left) {
                    break;
                }

                $list->insert($folder);
                $limit_left--;
            }

            $total += $folders->count();
            $offset_left -= $total;
        }

        if ((empty($type) || (in_array('file', $type) || in_array('image', $type))))
        {
            $data           = $state->getValues();
            $data['offset'] = $offset_left < 0 ? 0 : $offset_left;

            $files = $this->getObject('com:files.model.files')->setState($data);

            foreach ($files->fetch() as $file)
            {
                if (!$limit_left) {
                    break;
                }

                $list->insert($file);
                $limit_left--;
            }

            $total += $files->count();
        }

        $this->_count = $total;

        return $list;
    }

    /**
     * Reset the cached container object if container changes
     *
     * @param Library\ModelContext $context
     */
    protected function _afterReset(Library\ModelContext $context)
    {
        $modified = (array) Library\ObjectConfig::unbox($context->modified);
        if (in_array('container', $modified)) {
            $this->_container = null;
        }
    }

    /**
     * Returns the current container row
     *
     * @return ModelEntityContainer
     * @throws \UnexpectedValueException
     */
    public function getContainer()
    {
        $state = $this->getState();

        if(!isset($this->_container) && $state->container)
        {
            //Set the container
            $container = $this->getObject('com:files.model.containers')->slug($state->container)->fetch();

            if (!is_object($container) || !count($container) || $container->isNew()) {
                throw new \UnexpectedValueException('Invalid container: ' . $state->container);
            }

            $this->_container = $container;
        }

        return $this->_container;
    }

    public function getPath()
    {
        $state = $this->getState();

        $path = '';

        if ($container = $this->getContainer()) {
            $path = $container->fullpath;
        }

        if (!empty($state->folder) && $state->folder != '/') {
            $path .= '/'.ltrim($state->folder, '/');
        }

        return $path;
    }
}
