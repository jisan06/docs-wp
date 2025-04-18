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
 * Abstract Object Decorator
 *
 * The abstract object decorator allows to decorate any object. To decorate an object that extends from
 * Object use ObjectDecorator instead.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Object\Decorator
 */
abstract class ObjectDecoratorAbstract implements ObjectDecoratorInterface
{
    /**
     * Class methods
     *
     * @var array
     */
    private $__methods = array();

    /**
     *  The object being decorated
     *
     * @var Object
     */
    private $__delegate;

    /**
     * Constructor
     *
     * @param  ObjectConfig  $config  A ObjectConfig object with optional configuration options
     */
    public function __construct(ObjectConfig $config)
    {
        //Initialise the object
        $this->_initialize($config);

        //Set the delegate
        if(isset($config->delegate)) {
            $this->setDelegate($config->delegate);
        }
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
            'delegate' => null,
        ));
    }

    /**
     * Set the decorated object
     *
     * @return object
     */
    public function getDelegate()
    {
        return $this->__delegate;
    }

    /**
     * Set the decorated object
     *
     * @param   object $delegate The decorated object
     * @return  ObjectDecoratorAbstract
     * @throws  \InvalidArgumentException If the delegate is not an object
     */
    public function setDelegate($delegate)
    {
        if (!is_object($delegate)) {
            throw new \InvalidArgumentException('Delegate needs to be an object, '.gettype($delegate).' given');
        }

        $this->__delegate = $delegate;
        return $this;
    }

    /**
     * Decorate Notifier
     *
     * This function is called when an object is being decorated. It will get the object passed in.
     *
     * @param object $delegate The object being decorated
     * @return void
     * @throws  \InvalidArgumentException If the delegate is not an object
     */
    public function onDecorate($delegate)
    {
        $this->setDelegate($delegate);
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
        $delegate = $this->getDelegate();

        if($delegate instanceof ObjectHandlable) {
            $handle = $delegate->getHandle();
        } else {
            $handle = spl_object_hash($this);
        }

        return $handle;
    }

    /**
     * Get a list of all the available methods
     *
     * This function returns an array of all the methods, both native and mixed.
     *
     * @return array An array
     */
    public function getMethods()
    {
        if (!$this->__methods)
        {
            $methods  = array();
            $delegate = $this->getDelegate();

            if (!($delegate instanceof ObjectMixable))
            {
                $reflection = new \ReflectionClass($delegate);
                foreach ($reflection->getMethods() as $method) {
                    $methods[] = $method->name;
                }
            }
            else $methods = $delegate->getMethods();

            $this->__methods = $methods;
        }

        return $this->__methods;
    }

    /**
     * Check if a mixed method exists
     *
     * @param string $name The name of the method
     * @return mixed
     */
    public function isMixedMethod($name)
    {
        $result   = false;
        $delegate = $this->getDelegate();

        if (!($delegate instanceof ObjectMixable)) {
            $result = $delegate->isMixedMethod($name);
        }

        return $result;
    }

    /**
     * Overloaded set function
     *
     * @param  string $key   The variable name
     * @param  mixed  $value The variable value.
     * @return mixed
     */
    public function __set($key, $value)
    {
        $this->getDelegate()->$key = $value;
    }

    /**
     * Overloaded get function
     *
     * @param  string $key  The variable name.
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getDelegate()->$key;
    }

    /**
     * Overloaded isset function
     *
     * Allows testing with empty() and isset() functions
     *
     * @param  string $key The variable name
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->getDelegate()->$key);
    }

    /**
     * Overloaded isset function
     *
     * Allows unset() on object properties to work
     *
     * @param string $key The variable name.
     * @return void
     */
    public function __unset($key)
    {
        if (isset($this->getDelegate()->$key)) {
            unset($this->getDelegate()->$key);
        }
    }

    /**
     * Overloaded call function
     *
     * @param  string     $method    The function name
     * @param  array      $arguments The function arguments
     * @throws \BadMethodCallException     If method could not be found
     * @return mixed The result of the function
     */
    public function __call($method, $arguments)
    {
        $delegate = $this->getDelegate();

        //Check if the method exists
        if ($delegate instanceof ObjectMixable)
        {
            $methods = $delegate->getMethods();
            $exists = in_array($method, $methods);
        }
        else $exists = method_exists($delegate, $method);

        //Call the method if it exists
        if ($exists)
        {
            $result = $delegate->$method(...$arguments);

            //Allow for method chaining through the decorator
            $class = get_class($delegate);
            if ($result instanceof $class) {
                return $this;
            }

            return $result;
        }

        throw new \BadMethodCallException('Call to undefined method :' . $method);
    }
}