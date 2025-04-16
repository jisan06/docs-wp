<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelBehaviorFileable extends Library\ModelBehaviorAbstract
{
    protected function _fetchFiles(Library\ModelContextInterface $context)
    {
        $state = $this->getState();

        $files = $this->getObject('com:easydoc.model.files')->setState($state->getValues())->fetch();
        $data  = [];

        foreach ($files as $file) {
            $data[] = [
                'container' => $state->container,
                'folder'    => $state->folder,
                'name'      => $file->name
            ];
        }

        $identifier         = $this->getMixer()->getIdentifier()->toArray();
        $identifier['path'] = ['model', 'entity'];

        $context->result = $this->getObject($identifier, ['data' => $data]);

        return false;
    }

    protected function _fetchFolders(Library\ModelContextInterface $context)
    {
        $state = $this->getState();

        $entities = $this->getObject('com:easydoc.model.folders')->setState($state->getValues())->fetch();
        $folders = [];
        foreach ($entities as $e) {
            $folders[] = $e->path;
        }

        $identifier         = $this->getMixer()->getIdentifier()->toArray();
        $identifier['path'] = ['model', 'entity'];
        $collection = $this->getObject($identifier);

        foreach ($folders as $folder)
        {
            $hierarchy = [];
            if ($state->tree)
            {
                $hierarchy = explode('/', dirname($folder));
                if (count($hierarchy) === 1 && $hierarchy[0] === '.') {
                    $hierarchy = [];
                }
            }

            $base = \Foliokit\basename($folder);
            $name = strpos($folder, '/') !== false ? substr($folder, strrpos($folder, '/')+1) : $base;

            $properties = [
                'container' => $state->container,
                'folder' 	=> $hierarchy ? implode('/', $hierarchy) : $state->folder,
                'name' 		=> $name,
                'hierarchy' => $hierarchy
            ];

            $collection->create($properties);
        }

        $context->result = $collection;

        return false;
    }

    protected function _fetchNodes(Library\ModelContextInterface $context)
    {
        $state = $context->state;

        $type = !empty($state->types) ? (array)$state->types : [];

        $list = $this->getObject('com:files.model.entity.nodes');

        // Special case for limit=0. We set it to -1 so loop goes on till end since limit is a negative value
        $limit_left  = $state->limit ? : -1;
        $offset_left = $state->offset;

        if ($limit_left < 0) {

        }

        if (empty($type) || in_array('folder', $type))
        {
            $data           = $state->getValues();
            $data['limit'] = $limit_left === -1 ? 0 : $limit_left;

            $folders = $this->getObject('com:files.model.folders')->setState($data);

            $count = $folders->count();

            if ($offset_left > $count) {
                $offset_left -= $count;
            }
            else {
                $f = $folders->fetch();
                foreach ($f as $folder) {
                    $list->insert($folder);
                }

                if ($limit_left !== -1) {
                    $limit_left -= $list->count();
                }
            }
        }

        if ($limit_left && (empty($type) || (in_array('file', $type) || in_array('image', $type))))
        {
            $data           = $state->getValues();
            $data['limit'] = $limit_left < 0 ? 0 : $limit_left;
            $data['offset'] = $offset_left < 0 ? 0 : $offset_left;

            $files = $this->getObject('com:files.model.files')->setState($data)->fetch();

            foreach ($files as $file)
            {
                if ($state->limit && !$limit_left) {
                    break;
                }

                $list->insert($file);
                $limit_left--;
            }
        }

        $context->result = $list;

        return false;
    }

    protected function _countFiles(Library\ModelContextInterface $context)
    {
        $context->count = $this->getObject('com:easydoc.model.files')->setState($this->getState()->getValues())->count();

        return false;
    }

    protected function _countFolders(Library\ModelContextInterface $context)
    {
        $context->count = $this->getObject('com:easydoc.model.folders')->setState($this->getState()->getValues())->count();

        return false;
    }

    protected function _countNodes(Library\ModelContextInterface $context)
    {
        $state = $context->state;
        $type  = !empty($state->types) ? (array)$state->types : [];
        $count = 0;

        if (empty($type) || in_array('folder', $type)) {
            $count += $this->getObject('com:files.model.folders')->setState($this->getState()->getValues())->count();
        }

        if ((empty($type) || (in_array('file', $type) || in_array('image', $type)))) {
            $count += $this->getObject('com:files.model.files')->setState($this->getState()->getValues())->count();
        }

        $context->count = $count;

        return false;
    }

    protected function _beforeFetch(Library\ModelContextInterface $context)
    {
        $name = $this->getMixer()->getIdentifier()->getName();

        if ($name === 'files') {
            return $this->_fetchFiles($context);
        }
        else if ($name === 'folders') {
            return $this->_fetchFolders($context);
        }
        else if ($name === 'nodes') {
            return $this->_fetchNodes($context);
        }
    }


    protected function _beforeCount(Library\ModelContext $context)
    {
        $name = $this->getMixer()->getIdentifier()->getName();

        if ($name === 'files') {
            return $this->_countFiles($context);
        }
        else if ($name === 'folders') {
            return $this->_countFolders($context);
        }
        else if ($name === 'nodes') {
            return $this->_countNodes($context);
        }
    }
}