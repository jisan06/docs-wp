<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Library;

/**
 * Cache Class Registry
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Class\Registry
 */
class ClassRegistryCache extends ClassRegistry
{
    /**
     * The registry cache namespace
     *
     * @var boolean
     */
    protected $_namespace = 'foliokit';

    /**
     * Constructor
     *
     * @return ClassRegistryCache
     * @throws \RuntimeException    If the APC PHP extension is not enabled or available
     */
    public function __construct()
    {
        if (!static::isSupported()) {
            throw new \RuntimeException('Unable to use ClassRegistryCache. APCu is not enabled.');
        }
    }

    /**
     * Checks if the APC PHP extension is enabled
     * @return bool
     */
    public static function isSupported()
    {
        return extension_loaded('apcu') && apcu_enabled();
    }

    /**
     * Get the registry cache namespace
     *
     * @param string $namespace
     * @return void
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
    }

    /**
     * Get the registry cache namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Get an item from the array by offset
     *
     * @param   int     $offset The offset
     * @return  mixed   The item from the array
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if(!parent::offsetExists($offset))
        {
            if($result = apcu_fetch($this->getNamespace().'-class_'.$offset)) {
                parent::offsetSet($offset, $result);
            }
        }
        else $result = parent::offsetGet($offset);

        return $result;
    }

    /**
     * Set an item in the array
     *
     * @param   int     $offset The offset of the item
     * @param   mixed   $value  The item's value
     * @return  object  ObjectArray
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        apcu_store($this->getNamespace().'-class_'.$offset, $value);

        parent::offsetSet($offset, $value);
    }

    /**
     * Check if the offset exists
     *
     * @param   int   $offset The offset
     * @return  bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        if(false === $result = parent::offsetExists($offset)) {
            $result = apcu_exists($this->getNamespace().'-class_'.$offset);
        }

        return $result;
    }

    /**
     * Unset an item from the array
     *
     * @param   int     $offset
     * @return  void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        apcu_delete($this->getNamespace().'-class_'.$offset);

        if(parent::offsetExists($offset)){
            parent::offsetUnset($offset);
        }
    }

    /**
     * Clears APC cache
     *
     * @return $this
     */
    public function clear()
    {
        // Clear user cache
        apcu_clear_cache();

        return parent::clear();
    }
}