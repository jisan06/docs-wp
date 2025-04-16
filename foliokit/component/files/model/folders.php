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
 * Folders Model
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ModelFolders extends ModelNodes
{
	public function __construct(Library\ObjectConfig $config)
	{
		parent::__construct($config);

		$this->getState()->insert('tree', 'boolean', false);
	}

    protected function _actionFetch(Library\ModelContext $context)
    {
        $state = $this->getState();

        $folders = $this->getObject('com:files.adapter.iterator')->getFolders([
            'path'    => $this->getPath(),
            'recurse' => !!$state->tree,
            'filter'  => [$this, 'iteratorFilter'],
            'map'     => [$this, 'iteratorMap'],
            'sort'    => $state->sort
        ]);

        if ($folders === false) {
            throw new \UnexpectedValueException('Invalid folder');
        }

        $this->_count = count($folders);

        if (strtolower($state->direction) == 'desc') {
            $folders = array_reverse($folders);
        }

        $folders = array_slice($folders, $state->offset ?: 0, $state->limit ? $state->limit : $this->_count);

        $identifier         = $this->getIdentifier()->toArray();
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

        return $collection;
	}

    protected function _actionCount(Library\ModelContext $context)
    {
        if (!isset($this->_count)) {
            $this->fetch();
        }

        return $this->_count;
    }

	public function iteratorMap($path)
	{
		$path = str_replace('\\', '/', $path);

		if ($container = $this->getContainer()) {
            $path = str_replace($container->fullpath.'/', '', $path);
        }

		return $path;
	}

	public function iteratorFilter($path)
	{
        $state    = $this->getState();
		$filename = \Foliokit\basename($path);

        if ($filename && $filename[0] === '.') {
            return false;
        }

		if ($state->name)
		{
            if (!in_array($filename, (array) $state->name) && !in_array(FilterPath::normalizePath($filename), (array) $state->name)) {
                return false;
            }
		}

		if ($state->search && stripos($filename, $state->search) === false) {
			return false;
		}
	}
}
