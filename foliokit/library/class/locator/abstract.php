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
 * Abstract Loader Adapter
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Class\Locator
 */
abstract class ClassLocatorAbstract implements ClassLocatorInterface
{
    /**
     * The locator name
     *
     * @var string
     */
    protected static $_name = '';

    /**
     * Locator namespaces
     *
     * @var array
     */
    protected $_namespaces = array();

    /**
     * Register a namespace
     *
     * @param  string       $namespace
     * @param  string|array $path(s) The location of the namespace
     * @return ClassLocatorInterface
     */
    public function registerNamespace($namespace, $path)
    {
        $namespace = trim($namespace, '\\');
        $this->_namespaces[$namespace] = (array) $path;

        krsort($this->_namespaces, SORT_STRING);

        return $this;
    }

    /**
     * Get a namespace path(s)
     *
     * @param string $namespace The namespace
     * @return array|false The namespace path(s) or FALSE if the namespace does not exist.
     */
    public function getNamespacePaths($namespace)
    {
        $namespace = trim($namespace, '\\');
        return isset($this->_namespaces[$namespace]) ?  $this->_namespaces[$namespace] : false;
    }

    /**
     * Get the registered namespaces
     *
     * @return array An array with namespaces as keys and path as value
     */
    public function getNamespaces()
    {
        return $this->_namespaces;
    }

    /**
     * Get locator name
     *
     * @return string
     */
    public static function getName()
    {
        return static::$_name;
    }
}
