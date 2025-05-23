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
 * Node Entity
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ModelEntityNode extends Library\ModelEntityAbstract
{
	protected $_adapter;

    protected $_container;

    protected static $_container_cache = [];

    protected $_uri = null;

	public function __construct(Library\ObjectConfig $config)
	{
		parent::__construct($config);

        // Mixin the behavior interface
        $this->mixin('lib:behavior.mixin', $config);

		if ($config->validator !== false)
		{
			if ($config->validator === true) {
				$config->validator = 'com:files.database.validator.'.$this->getIdentifier()->name;
			}

			$this->addCommandHandler($this->getObject($config->validator));
		}
	}

	protected function _initialize(Library\ObjectConfig $config)
	{
		$config->append([
			'validator' => true
        ]);

		parent::_initialize($config);
	}

	public function isNew()
	{
		return (empty($this->name) && !$this->_uri) || !$this->_adapter->exists();
	}

	public function copy()
	{
		$context = $this->getContext();
		$context->result = false;

		if ($this->invokeCommand('before.copy', $context) !== false)
		{
			$context->result = $this->_adapter->copy($this->destination_fullpath);
			$this->invokeCommand('after.copy', $context);
        }

		if ($context->result === false) {
			$this->setStatus(Library\Database::STATUS_FAILED);
		}
		else
		{
			if (!is_null($this->destination_folder)) {
				$this->folder = $this->destination_folder;
			}
			if ($this->destination_name) {
				$this->name = $this->destination_name;
			}

			$this->setStatus($this->overwritten ? Library\Database::STATUS_UPDATED : Library\Database::STATUS_CREATED);
		}

		return $context->result;
	}

	public function move()
	{
		$context = $this->getContext();
		$context->result = false;

		if ($this->invokeCommand('before.move', $context) !== false)
		{
			$context->result = $this->_adapter->move($this->destination_fullpath);
			$this->invokeCommand('after.move', $context);
        }

		if ($context->result === false) {
			$this->setStatus(Library\Database::STATUS_FAILED);
		}
		else
		{
			if (!is_null($this->destination_folder)) {
				$this->folder = $this->destination_folder;
			}

			if ($this->destination_name) {
				$this->name = $this->destination_name;
			}

			$this->setStatus($this->overwritten ? Library\Database::STATUS_UPDATED : Library\Database::STATUS_CREATED);
		}

		return $context->result;
	}

	public function delete()
	{
		$context = $this->getContext();
		$context->result = false;

		if ($this->invokeCommand('before.delete', $context) !== false)
		{
			$context->result = $this->_adapter->delete();
			$this->invokeCommand('after.delete', $context);
        }

		if ($context->result === false) {
			$this->setStatus(Library\Database::STATUS_FAILED);
		}
		else $this->setStatus(Library\Database::STATUS_DELETED);

		return $context->result;
	}

    public function getPropertyFullpath()
    {
        return $this->_adapter->getRealPath();
    }

    public function getPropertyPath()
    {
        $path = ($this->folder ? $this->folder . '/' : '') . $this->name;

        if ($this->getContainer()) {
            $path = trim($path, '/\\'); // Make path relative to container
        }

        return $path;
    }

    public function getPropertyDestinationPath()
    {
        $folder = isset($this->destination_folder) ? $this->destination_folder . '/' : (!empty($this->folder) ? $this->folder . '/' : '');
        $name   = isset($this->destination_name) ? $this->destination_name : $this->name;

        $path = $folder . $name;

        if ($this->getContainer()) {
            $path = trim($path, '/\\'); // Make path relative to container
        }

        return $path;
    }

    public function getPropertyDestinationFullpath()
    {
        $path = $this->destination_path;

        if ($container = $this->getContainer()) {
            $path = $container->fullpath . '/' . $path;
        }

        return $path;
    }

    public function getPropertyRelativePath()
    {
        $path = $this->path;

        if ($container = $this->getContainer()) {
            $path = $container->relative_path . '/' . $path;
        } else {
            $path = str_replace(\Foliokit::getInstance()->getRootPath() . '/', '', $path);
        }

        return $path;
    }

    public function getPropertyAdapter()
    {
        return $this->_adapter;
    }

	public function setProperty($column, $value, $modified = true)
	{
		parent::setProperty($column, $value, $modified = true);

		if ($column == 'uri')
        {
            $this->_uri = $value; // Keep URI value on object property

            $parts = $this->getObject('com:files.model.state.parser.url')->parse($value);

            Library\ObjectArray::offsetSet('name', basename($parts->path));
            Library\ObjectArray::offsetSet('folder', dirname($parts->path));

            if ($container = $parts->container) {
                Library\ObjectArray::offsetSet('container', basename($container));
            }
        }

        if (in_array($column, ['folder', 'name', 'container'])) $this->_uri = null; // Reset URI property

        if ($column === 'container' || $column === 'uri' || in_array($column, ['folder', 'name'])) {
			$this->setAdapter();
		}
	}

	public function getPropertyUri()
    {
        if (!$this->_uri)
        {
            $path = ($this->folder ? $this->folder . '/' : '') . $this->name;

            if ($container = $this->getContainer()) {
                $path = $container->slug . '/' . $path;
            }

            if ($path) {
                $this->_uri = sprintf('file://%s', $path);
            }
        }

        return $this->_uri;
    }

    public function getContainer()
    {
        if(!$this->_container instanceof ModelEntityContainer && ($container = $this->container))
        {
            // TODO Is this check really needed here?
            if (is_string($container))
            {
                if (!isset(self::$_container_cache[$container])) {
                    self::$_container_cache[$container] = $this->getObject('com:files.model.containers')->slug($container)->fetch();
                }

                $container = self::$_container_cache[$container];
            }

            if (!is_object($container) || !count($container) || $container->isNew()) {
                throw new \UnexpectedValueException('Invalid container');
            }

            $this->_container = $container;
        }

        return $this->_container;
    }

    public function isLocal()
    {
        return $this->_adapter && $this->_adapter->isLocal();
    }

	public function setAdapter()
	{
		$type = $this->getIdentifier()->name;

		$path = ($this->folder ? $this->folder . '/' : '') . $this->name;

        if ($container = $this->getContainer()) {
            $path = $container->fullpath . '/' . $path;
        } else {
            $path = $this->uri ?: $path;
        }

        $this->_adapter = $this->getObject(sprintf('com:files.adapter.%s', $type), ['path' => $path]);

		unset($this->_data['fullpath']);
		unset($this->_data['metadata']);

		return $this;
	}

    public function setProperties($data, $modified = true)
    {
        $result = parent::setProperties($data, $modified);

        if (isset($data['container'])) {
            $this->setAdapter();
        }

        return $result;
    }

    public function toArray()
    {
        $data = parent::toArray();

        foreach ($data as $key => $value)
        {
            if ($value instanceof Library\ModelEntityAbstract || $value instanceof Library\ModelEntityComposite) {
                $data[$key] = $value->toArray();
            }
        }

        unset($data['action']);
        unset($data['option']);
        unset($data['component']);
        unset($data['format']);
        unset($data['view']);

        if ($container = $this->getContainer()) {
            $data['container'] = $container->slug;
        }

        $data['type'] = $this->getIdentifier()->name;
        $data['path'] = $this->path;
        $data['uri']  = $this->uri;

        return $data;
    }

    public function count()
    {
        return (int) !$this->isNew();
    }

    /**
     * Get the context
     *
     * @return Library\Command
     */
    public function getContext()
    {
        $context = new Library\DatabaseContext();
        $context->setSubject($this);

        return $context;
    }

    public function isLockable()
    {
        return false;
    }
}
