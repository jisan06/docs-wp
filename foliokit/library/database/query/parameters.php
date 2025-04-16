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
 * Delete Database Query
 *
 * @author  Gergo Erdosi <https://github.com/gergoerdosi>
 * @package EasyDocLabs\Library\Database\Query
 */
class DatabaseQueryParameters extends ObjectArray
{
    /**
     * Constructor
     *
     * @param ObjectConfig $config  An optional ObjectConfig object with configuration options
     */
    public function __construct(ObjectConfig $config)
    {
        parent::__construct($config);

        $parameters = ObjectConfig::unbox($config->parameters);
        foreach ($parameters as $key => $values) {
            $this->set($key, $values);
        }
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   ObjectConfig $config An optional ObjectConfig object with configuration options
     */
    protected function _initialize(ObjectConfig $config)
    {
        $config->append(array(
            'parameters' => array(),
        ));

        parent::_initialize($config);
    }

    /**
     * Get a query parameter
     *
     * @param   string  $name The parameter name.
     * @return  string  The corresponding value.
     */
    public function get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * Set a query parameter by name
     *
     * @param   string  $name  The parameter name
     * @param   mixed   $value The value for the parameter
     * @return  $this
     */
    public function set($name, $value)
    {
        $this->offsetSet($name, $value);
        return $this;
    }

    /**
     * Test existence of a parameter
     *
     * @param  string  $name The parameter name
     * @return boolean
     */
    public function has($name)
    {
        return $this->offsetExists($name);
    }

    /**
     * Unset a key
     *
     * @param   string  $name The parameter name
     * @return  $this
     */
    public function remove($name)
    {
        $this->offsetUnset($name);
        return $this;
    }
}