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
 * Abstract Database Row
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Database\Row
 */
abstract class DatabaseRowAbstract extends ObjectArray implements DatabaseRowInterface
{
    /**
     * List of computed properties
     *
     * @var array
     */
    private $__computed_properties;

    /**
     * List of modified properties
     *
     * @var array
     */
    private $__modified_properties;

    /**
     * Tracks if row data is new
     *
     * @var bool
     */
    private $__new = true;

    /**
     * The status
     *
     * Available row status values are defined as STATUS_ constants in Database
     *
     * @var string
     * @see Database
     */
    protected $_status = null;

    /**
     * The status message
     *
     * @var string
     */
    protected $_status_message = '';

    /**
     * The identity key
     *
     * @var string
     */
    protected $_identity_column;

    /**
     * Table object or identifier
     *
     * @var    string|object
     */
    protected $_table = false;

    /**
     * Constructor
     *
     * @param  ObjectConfig $config  An optional ObjectConfig object with configuration options.
     */
    public function __construct(ObjectConfig $config)
    {
        parent::__construct($config);

        //Set the table identifier
        $this->_table = $config->table;

        // Set the table identifier
        if (isset($config->identity_column)) {
            $this->_identity_column = $config->identity_column;
        }

        // Clear the row
        $this->clear();

        //Set the status
        if (isset($config->status)) {
            $this->setStatus($config->status);
        }

        // Set the row data
        if (isset($config->data))
        {
            $this->setProperties($config->data->toArray(), $this->isNew());
            unset($config->data);
        }

        //Set the status message
        if (!empty($config->status_message)) {
            $this->setStatusMessage($config->status_message);
        }
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param  ObjectConfig $config An optional ObjectConfig object with configuration options.
     * @return void
     */
    protected function _initialize(ObjectConfig $config)
    {
        $config->append(array(
            'table'           => $this->getIdentifier()->name,
            'data'            => null,
            'status'          => null,
            'status_message'  => '',
            'identity_column' => null
        ));

        parent::_initialize($config);
    }

    /**
     * Saves the row to the database.
     *
     * This performs an intelligent insert/update and reloads the properties with fresh data from the table on success.
     * Save prevent subsequent database updates if the entity was already updated previously and it was not
     * modified since.
     *
     * @return boolean If successful return TRUE, otherwise FALSE
     */
    public function save()
    {
        $result = false;

        if ($this->isConnected())
        {
            if (!$this->isNew())
            {
                if($this->getStatus() !== Database::STATUS_UPDATED) {
                    $result = $this->getTable()->update($this);
                }
            }
            else $result = $this->getTable()->insert($this);
        }

        return (bool) $result;
    }

    /**
     * Deletes the row form the database.
     *
     * @return boolean    If successful return TRUE, otherwise FALSE
     */
    public function delete()
    {
        $result = false;

        if ($this->isConnected())
        {
            if (!$this->isNew()) {
                $result = $this->getTable()->delete($this);
            }
        }

        return (bool) $result;
    }

    /**
     * Clear the row data using the defaults
     *
     * @return DatabaseRowInterface
     */
    public function clear()
    {
        $this->_data                 = array();
        $this->__modified_properties = array();
        $this->setStatus(NULL);

        if ($this->isConnected()) {
            $this->_data = $this->getTable()->getDefaults();
        }

        return $this;
    }

    /**
     * Mixin an object
     *
     * Reset the computed_properties after a behavior has been mixed that has mixable methods
     *
     * @param   mixed $identifier An ObjectIdentifier, identifier string or object implementing ObjectMixableInterface
     * @param  array $config  An optional associative array of configuration options
     * @return  ObjectMixinInterface
     * @throws  ObjectExceptionInvalidIdentifier If the identifier is not valid
     * @throws  \UnexpectedValueException If the mixin does not implement the ObjectMixinInterface
     */
    public function mixin($mixin, $config = array())
    {
        $mixin = parent::mixin($mixin, $config);

        //Reset the computed properties array
        $methods = $mixin->getMixableMethods();
        if(!empty($methods)) {
            $this->__computed_properties = null;
        }

        return $mixin;
    }

    /**
     * Get a property
     *
     * Method provides support for computed properties by calling an getProperty[CamelizedName] if it exists. The getter
     * should return the computed value to get.
     *
     * @param   string  $name The property name
     * @return  mixed   The property value.
     */
    public function getProperty($name)
    {
        //Handle computed properties
        if(!parent::offsetExists($name) && $this->hasProperty($name))
        {
            $getter  = 'getProperty'.StringInflector::camelize($name);
            $methods = $this->getMethods();

            if(isset($methods[$getter])) {
                parent::offsetSet($name, $this->$getter());
            }
        }

        return parent::offsetGet($name);
    }

    /**
     * Set a property
     *
     * If the value is the same as the current value and the row is loaded from the database the value will not be set.
     * If the row is new the value will be (re)set and marked as modified.
     *
     * Method provides support for computed properties by calling an setProperty[CamelizedName] if it exists. The setter
     * should return the computed value to set.
     *
     * @param   string  $name       The property name.
     * @param   mixed   $value      The property value.
     * @param   boolean $modified   If TRUE, update the modified information for the property
     *
     * @return  $this
     */
    public function setProperty($name, $value, $modified = true)
    {
        if (!array_key_exists($name, $this->_data) || ($this->_data[$name] != $value))
        {
            $computed = $this->getComputedProperties();
            if(!in_array($name, $computed))
            {
                //Force computed properties to re-calculate
                foreach($computed as $property) {
                    parent::offsetUnset($property);
                }

                //Call the setter if it exists
                $setter  = 'setProperty'.StringInflector::camelize($name);
                $methods = $this->getMethods();

                if(isset($methods[$setter])) {
                    $value = $this->$setter($value);
                }

                //Set the property value
                parent::offsetSet($name, $value);

                //Mark the property as modified
                if($modified || $this->isNew())
                {
                    $this->__modified_properties[$name] = $name;
                    $this->setStatus(Database::STATUS_MODIFIED);
                }
            }
        }

        return $this;
    }

    /**
     * Test existence of a property
     *
     * @param  string  $name The property name.
     * @return boolean
     */
    public function hasProperty($name)
    {
        $result = false;

        //Handle computed properties
        if(!parent::offsetExists($name))
        {
            if(!empty($name))
            {
                $properties = $this->getComputedProperties();

                if(isset($properties[$name])) {
                    $result = true;
                }
            }
        }
        else $result = true;

        return $result;
    }

    /**
     * Remove a property
     *
     * This function will reset required properties to their default value, not required properties will be unset.
     *
     * @param   string  $name The property name.
     * @return  DatabaseRowAbstract
     */
    public function removeProperty($name)
    {
        if ($this->isConnected())
        {
            $column = $this->getTable()->getColumn($name);

            if (isset($column) && $column->required) {
                $this->setProperty($name, $column->default);
            }
            else
            {
                parent::offsetUnset($name);
                unset($this->__modified_properties[$name]);
            }
        }

        return $this;
    }

    /**
     * Get the properties
     *
     * @param   boolean  $modified If TRUE, only return the modified data.
     * @return  array   An associative array of the row properties
     */
    public function getProperties($modified = false)
    {
        $properties = $this->_data;

        if ($modified) {
            $properties = array_intersect_key($properties, $this->__modified_properties);
        }

        return $properties;
    }

    /**
     * Set the properties
     *
     * @param   mixed   $properties  Either and associative array, an object or a DatabaseRow
     * @param   boolean $modified    If TRUE, update the modified information for each property being set.
     * @return  DatabaseRowAbstract
     */
    public function setProperties($properties, $modified = true)
    {
        if ($properties instanceof DatabaseRowInterface) {
            $properties = $properties->getProperties(false);
        }

        foreach ($properties as $property => $value) {
            $this->setProperty($property, $value, $modified);
        }

        return $this;
    }

    /**
     * Get a list of the computed properties
     *
     * @return array An array
     */
    public function getComputedProperties()
    {
        if (!$this->__computed_properties)
        {
            $properties = array();

            foreach ($this->getMethods() as $method)
            {
                if (substr($method, 0, 11) == 'getProperty' && $method !== 'getProperty')
                {
                    $property = StringInflector::underscore(substr($method, 11));
                    $properties[$property] = $property;
                }
            }

            $this->__computed_properties = $properties;
        }

        return $this->__computed_properties;
    }

    /**
     * Returns the status
     *
     * @return string The status
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Set the status
     *
     * @param   string|null  $status The status value or NULL to reset the status
     * @return  DatabaseRowAbstract
     */
    public function setStatus($status)
    {
        if($status === Database::STATUS_CREATED) {
            $this->__new = false;
        }

        if($status === Database::STATUS_DELETED) {
            $this->__new = true;
        }

        if($status === Database::STATUS_FETCHED) {
            $this->__new = false;
        }

        $this->_status = $status;
        return $this;
    }

    /**
     * Returns the status message
     *
     * @return string The status message
     */
    public function getStatusMessage()
    {
        return $this->_status_message;
    }

    /**
     * Set the status message
     *
     * @param   string $message The status message
     * @return  DatabaseRowAbstract
     */
    public function setStatusMessage($message)
    {
        $this->_status_message = $message;
        return $this;
    }

    /**
     * Gets the identity key
     *
     * @return string
     */
    public function getIdentityColumn()
    {
        return $this->_identity_column;
    }

    /**
     * Get a handle for this object
     *
     * This function returns an unique identifier for the object. This id can be used as a hash key for storing objects
     * or for identifying an object
     *
     * @return string A string that is unique
     */
    public function getHandle()
    {
        if(!$handle = $this->getProperty($this->getIdentityColumn())) {
            $handle = parent::getHandle();
        }

        return $handle;
    }

    /**
     * Get a new iterator
     *
     * @return  \ArrayIterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator(array($this));
    }

    /**
     * Method to get a table object
     *
     * Function catches DatabaseTableExceptions that are thrown for tables that
     * don't exist. If no table object can be created the function will return FALSE.
     *
     * @return DatabaseTableAbstract
     */
    public function getTable()
    {
        if ($this->_table !== false)
        {
            if (!($this->_table instanceof DatabaseTableInterface))
            {
                //Make sure we have a table identifier
                if (!($this->_table instanceof ObjectIdentifier)) {
                    $this->setTable($this->_table);
                }

                try {
                    $this->_table = $this->getObject($this->_table);
                } catch (DatabaseException $e) {
                    $this->_table = false;
                }
            }
        }

        return $this->_table;
    }

    /**
     * Method to set a table object attached to the rowset
     *
     * @param    mixed    $table An object that implements ObjectInterface, ObjectIdentifier object
     *                           or valid identifier string
     * @throws  \UnexpectedValueException    If the identifier is not a table identifier
     * @return  DatabaseRowInterface
     */
    public function setTable($table)
    {
        if (!($table instanceof DatabaseTableInterface))
        {
            if (is_string($table) && strpos($table, '.') === false)
            {
                $identifier = $this->getIdentifier()->toArray();
                $identifier['path'] = array('database', 'table');
                $identifier['name'] = StringInflector::pluralize(StringInflector::underscore($table));

                $identifier = $this->getIdentifier($identifier);
            }
            else $identifier = $this->getIdentifier($table);

            if ($identifier->path[1] != 'table') {
                throw new \UnexpectedValueException('Identifier: ' . $identifier . ' is not a table identifier');
            }

            $table = $identifier;
        }

        $this->_table = $table;

        return $this;
    }

    /**
     * Checks if the row is new or not
     *
     * @return bool
     */
    public function isNew()
    {
        return (bool) $this->__new;
    }

    /**
     * Check if a the row or specific row property has been modified.
     *
     * If a specific property name is giving method will return TRUE only if this property was modified.
     *
     * @param   string $property The property name
     * @return  boolean
     */
    public function isModified($property = null)
    {
        $result = false;

        if($property)
        {
            if (isset($this->__modified_properties[$property]) && $this->__modified_properties[$property]) {
                $result = true;
            }
        }
        else $result = (bool) count($this->__modified_properties);

        return $result;
    }

    /**
     * Test the connected status of the row.
     *
     * @return    boolean    Returns TRUE if we have a reference to a live DatabaseTableAbstract object.
     */
    public function isConnected()
    {
        return (bool)$this->getTable();
    }

    /**
     * Return an associative array of the data
     *
     * Skip the properties that start with an underscore as they are considered private
     *
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();

        foreach(array_keys($data) as $key)
        {
            if (substr($key, 0, 1) === '_') {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Set a property
     *
     * @param   string  $property   The property name.
     * @param   mixed   $value      The property value.
     * @return  void
     */
    #[\ReturnTypeWillChange]
    final public function offsetSet($property, $value)
    {
        $this->setProperty($property, $value);
    }

    /**
     * Get a property
     *
     * @param   string  $property   The property name.
     * @return  mixed The property value
     */
    #[\ReturnTypeWillChange]
    final public function offsetGet($property)
    {
        return $this->getProperty($property);
    }

    /**
     * Check if a property exists
     *
     * @param   string  $property   The property name.
     * @return  boolean
     */
    #[\ReturnTypeWillChange]
    final public function offsetExists($property)
    {
        return $this->hasProperty($property);
    }

    /**
     * Remove a property
     *
     * @param   string  $property The property name.
     * @return  void
     */
    #[\ReturnTypeWillChange]
    final public function offsetUnset($property)
    {
        $this->removeProperty($property);
    }

    /**
     * Overridden to NOT clone the mixed methods as we want the behaviors to be shared between rows
     *
     * @return void
     */
    public function __clone()
    {
    }

    /**
     * Search the mixin method map and call the method or trigger an error
     *
     * This function implements a just in time mixin strategy. Available table behaviors are only mixed when needed.
     * Lazy mixing is triggered by calling DatabaseRowsetTable::is[Behaviorable]();
     *
     * @param  string     $method    The function name
     * @param  array      $arguments The function arguments
     * @throws \BadMethodCallException     If method could not be found
     * @return mixed The result of the function
     */
    public function __call($method, $arguments)
    {
        if ($this->isConnected())
        {
            $parts = StringInflector::explode($method);

            //Check if a behavior is mixed
            if ($parts[0] == 'is' && isset($parts[1]))
            {
                if(!$this->isMixedMethod($method))
                {
                    //Lazy mix behaviors
                    $behavior = strtolower($parts[1]);

                    if ($this->getTable()->hasBehavior($behavior)) {
                        $this->mixin($this->getTable()->getBehavior($behavior));
                    } else {
                        return false;
                    }
                }
            }
        }

        return parent::__call($method, $arguments);
    }
}
