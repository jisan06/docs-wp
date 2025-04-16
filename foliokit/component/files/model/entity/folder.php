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
 * Folder Database Row
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ModelEntityFolder extends ModelEntityNode implements Library\CommandCallbackDelegate
{
	/**
	 * Nodes object or identifier
	 *
	 * @var string|object
	 */
	protected $_children = null;

	/**
	 * Node object or identifier
	 *
	 * @var string|object
	 */
	protected $_parent   = null;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        if(isset($config->parent)) {
            $this->setParent($config->parent);
        }

        foreach($config->children as $child) {
            $this->insertChild($child);
        }
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'children'  => [],
            'parent'	=> null,
        ]);

        parent::_initialize($config);
    }

	/**
	 * Invoke a command handler
	 *
	 * @param string             $method    The name of the method to be executed
	 * @param Library\CommandInterface  $command   The command
	 * @return mixed Return the result of the handler.
	 */
	public function invokeCommandCallback($method, Library\CommandInterface $command)
	{
		return $this->$method($command);
	}

	/**
	 * Stores the parent contents before creating a folder
	 *
	 * @param Library\DatabaseContextInterface $context
	 */
	protected function _beforeSave(Library\DatabaseContextInterface $context)
	{
		if (!$context->siblings) {
			$context->siblings = [];
		}

		$parent = dirname($context->getSubject()->fullpath);

		if (file_exists($parent)) {
            $context->siblings[] = scandir(dirname($context->getSubject()->fullpath));
        }
	}

	/**
	 * Sets the folder name as created by the OS (encoding) in the filesystem
	 *
	 * @param Library\DatabaseContextInterface $context
	 */
	protected function _afterSave(Library\DatabaseContextInterface $context)
	{
		if ($context->siblings && count($context->siblings))
		{
			$siblings = Library\ObjectConfig::unbox($context->siblings);

			$name = array_diff(scandir(dirname($context->getSubject()->fullpath)), array_pop($siblings));

			if (count($name) == 1) {
				$this->name = current($name);
			}

			$context->siblings = $siblings;
		}
	}

	public function save()
	{
		$context = $this->getContext();
		$context->result = false;

		$is_new = $this->isNew();

		if ($this->invokeCommand('before.save', $context) !== false)
		{
			if ($this->isNew()) {
				$context->result = $this->_adapter->create();
			}

			$this->invokeCommand('after.save', $context);
		}

		if ($context->result === false) {
			$this->setStatus(Library\Database::STATUS_FAILED);
		}
		else $this->setStatus($is_new ? Library\Database::STATUS_CREATED : Library\Database::STATUS_UPDATED);

		return $context->result;
	}

	public function getProperties($modified = false)
	{
		$result = parent::getProperties($modified);

		if (isset($result['children']) && $result['children'] instanceof Library\ModelEntityInterface) {
			$result['children'] = $result['children']->getProperties();
		}

		return $result;
	}

	public function insertChild(Library\ModelEntityInterface $node)
	{
		//Track the parent
		$node->setParent($this);

		$this->getChildren()->insert($node);

		return $this;
	}

	public function hasChildren()
	{
		return is_null($this->_children) ? false : (boolean) count($this->_children);
	}

	/**
	 * Get the children entity
	 *
	 * @return	object
	 */
	public function getChildren()
	{
		if(!($this->_children instanceof Library\ModelEntityInterface))
		{
			$identifier         = $this->getIdentifier()->toArray();
			$identifier['path'] = ['model', 'entity'];
			$identifier['name'] = Library\StringInflector::pluralize($this->getIdentifier()->name);

			//The row default options
			$options  = [
                'identity_key' => $this->getIdentityKey()
            ];

			$this->_children = $this->getObject($identifier, $options);
		}

		return $this->_children;
	}

	/**
	 * Get the parent node
	 *
	 * @return	ModelEntityFolder
	 */
	public function getParent()
	{
		return $this->_parent;
	}

	/**
	 * Set the parent node
	 *
     * @param  ModelEntityFolder $node
	 * @return $this
	 */
	public function setParent($node)
	{
		$this->_parent = $node;

		return $this;
	}

    public function toArray()
    {
        $data = parent::toArray();

        if ($this->hasChildren()) {
            $data['children'] = $this->getChildren()->toArray();
        }

        return $data;
    }
}
