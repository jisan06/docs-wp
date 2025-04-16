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
 * Files Model
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ModelFiles extends ModelNodes
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['behaviors' => ['thumbnailable']]);

        parent::_initialize($config);
    }

    protected function _beforeFetch(Library\ModelContext $context)
    {
        $context->local = true;

        $state = $this->getState();

        if ($uri = $state->uri)
        {
            $parts = parse_url($uri);

            if (isset($parts['scheme']) && $parts['scheme'] !== 'file') {
                $context->local = false;
            }
        }
    }

    protected function _actionFetch(Library\ModelContext $context)
    {
        $state = $this->getState();

        if ($context->local)
        {
            $files = $this->getObject('com:files.adapter.iterator')->getFiles([
                'path'    => $this->getPath(),
                'exclude' => ['.svn', '.htaccess', 'web.config', '.git', 'CVS', 'index.html', '.DS_Store', 'Thumbs.db', 'Desktop.ini'],
                'filter'  => [$this, 'iteratorFilter'],
                'map'     => [$this, 'iteratorMap'],
                'sort'    => $state->sort
            ]);

            if ($files === false) {
                throw new \UnexpectedValueException('Invalid folder');
            }
        }
        else $files = [$state->uri];


        $this->_count = count($files);

        if (strtolower($state->direction) == 'desc') {
            $files = array_reverse($files);
        }

        $results = array_slice($files, $state->offset ?: 0, $state->limit ? $state->limit : $this->_count);
        $files   = [];

        foreach ($results as $result) {
            $files[] = $context->local ? ['name' => $result] : ['uri' => $result];
        }

        $context->files = $files;

        if ($this->invokeCommand('before.createset', $context) !== false)
        {
            $context->set = $this->_actionCreateSet($context);
            $this->invokeCommand('after.createset', $context);
        }

        return $context->set;
    }

    protected function _actionCreateSet(Library\ModelContext $context)
    {
        $state = $context->getState();

        $data = [];

        foreach ($context->files as $file)
        {
            if ($context->local)
            {
                $file->append([
                    'container' => $state->container,
                    'folder'    => $state->folder
                ]);
            }

            $data[] = $file->toArray();
        }

        $identifier         = $this->getIdentifier()->toArray();
        $identifier['path'] = ['model', 'entity'];

        return $this->getObject($identifier, ['data' => $data]);
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
		return \Foliokit\basename($path);
	}

	public function iteratorFilter($path)
	{
        $state     = $this->getState();
		$filename  = \Foliokit\basename($path);
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($filename && $filename[0] === '.') {
            return false;
        }

        if ($state->name)
        {
            if (!in_array($filename, (array) $state->name) && !in_array(FilterPath::normalizePath($filename), (array) $state->name)) {
				return false;
			}
		}

		if ($state->types)
        {
			if ((in_array($extension, ModelEntityFile::$image_extensions) && !in_array('image', (array) $state->types))
			|| (!in_array($extension, ModelEntityFile::$image_extensions) && !in_array('file', (array) $state->types))
			) {
				return false;
			}
		}

		if ($state->search && stripos($filename, $state->search) === false) {
            return false;
        }
	}
}
