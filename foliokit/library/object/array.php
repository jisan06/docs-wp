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
 * Object Array
 *
 * The ObjectArray class provides provides the main functionality of an array and at the same time implement the
 * features of Object
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Object
 */
class ObjectArray extends ObjectAbstract implements \IteratorAggregate, \ArrayAccess, \Serializable, \Countable
{
    /**
     * The data for each key in the array (key => value).
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Constructor
     *
     * @param ObjectConfig $config  An optional ObjectConfig object with configuration options
     * @return ObjectArray
     */
    public function __construct(ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_data = ObjectConfig::unbox($config->data);
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   ObjectConfig $config An optional ObjectConfig object with configuration options
     * @return  void
     */
    protected function _initialize(ObjectConfig $config)
    {
        $config->append(array(
            'data' => array(),
        ));

        parent::_initialize($config);
    }

    /**
     * Get an item from the array by offset
     *
     * Required by interface ArrayAccess
     *
     * @param   int     $offset
     * @return  mixed The item from the array
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        $result = null;

        if (isset($this->_data[$offset])) {
            $result = $this->_data[$offset];
        }

        return $result;
    }

    /**
     * Set an item in the array
     *
     * Required by interface ArrayAccess
     *
     * @param   int     $offset
     * @param   mixed   $value
     * @return  void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->_data[] = $value;
        } else {
            $this->_data[$offset] = $value;
        }
    }

    /**
     * Check if the offset exists
     *
     * Required by interface ArrayAccess
     *
     * @param   int   $offset
     * @return  bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_data);
    }

    /**
     * Unset an item in the array
     *
     * All numerical array keys will be modified to start counting from zero while literal keys won't be touched.
     *
     * Required by interface ArrayAccess
     *
     * @param   int     $offset
     * @return  void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }

    /**
     * Get a new iterator
     *
     * @return  \ArrayIterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator($this->_data);
    }

    /**
     * Serialize
     *
     * Required by interface Serializable
     *
     * Note: Remove when required PHP version is 7.4+
     * See: https://php.watch/versions/8.1/serializable-deprecated
     * 
     * @return  string
     */
    public function serialize()
    {
        return serialize($this->_data);
    }

    /**
     * PHP 8.1 compatible serialize method
     *
     * @return array
     */
    public function __serialize(): array
    {
        return $this->_data;
    }

    /**
     * Unserialize
     *
     * Required by interface Serializable
     * 
     * Note: Remove when required PHP version is 7.4+
     * See: https://php.watch/versions/8.1/serializable-deprecated
     *
     * @param   string  $data
     */
    public function unserialize($data)
    {
        $this->_data = unserialize($data);
    }

    /**
     * PHP 8.1 compatible unserialize method
     *
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->_data = $data;
    }

    /**
     * Returns the number of items
     *
     * Required by interface Countable
     *
     * @return int The number of items
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->_data);
    }

    /**
     * Set the data from an array
     *
     * @param array $data An associative array of data
     * @return $this
     */
    public static function fromArray(array $data)
    {
        return new static(new ObjectConfig(array('data' => $data)));
    }

    /**
     * Return an associative array of the data
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_data;
    }

    /**
     * Get a value by key
     *
     * @param   string  $key The key name.
     * @return  string  The corresponding value.
     */
    final public function __get($key)
    {
        return $this->offsetGet($key);
    }

    /**
     * Set a value by key
     *
     * @param   string  $key   The key name
     * @param   mixed   $value The value for the key
     * @return  void
     */
    final public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Test existence of a key
     *
     * @param  string  $key The key name
     * @return boolean
     */
    final public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Unset a key
     *
     * @param   string  $key The key name
     * @return  void
     */
    final public function __unset($key)
    {
        $this->offsetUnset($key);
    }
}
