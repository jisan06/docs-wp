<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Library;

require_once dirname(__FILE__) . '/interface.php';
require_once dirname(__FILE__) . '/../locator/interface.php';
require_once dirname(__FILE__) . '/../locator/abstract.php';
require_once dirname(__FILE__) . '/../locator/library.php';
require_once dirname(__FILE__) . '/../registry/interface.php';
require_once dirname(__FILE__) . '/../registry/registry.php';
require_once dirname(__FILE__) . '/../registry/cache.php';

/**
 * Loader
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Class
 */
final class ClassLoader implements ClassLoaderInterface
{
    /**
     * The class container
     *
     * @var array
     */
    private $__registry = null;

    /**
     * The class locators
     *
     * @var array
     */
    protected $_locators = array();

    /**
     * Debug
     *
     * @var boolean
     */
    protected $_debug = false;

    /**
     * Constructor
     *
     * Prevent creating instances of this class by making the constructor private
     */
    public function __construct()
    {
        $this->__registry = new ClassRegistry();

        //Register the library locator
        $this->registerLocator(new ClassLocatorLibrary());

        //Register the loader with the PHP autoloader
        $this->register();
    }

    /**
     * Registers this instance as an autoloader.
     *
     * @param boolean $prepend Whether to prepend the autoloader or not
     * @return void
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'load'), true, $prepend);
    }

    /**
     * Unregisters the loader with the PHP autoloader.
     *
     * @return void
     *
     * @see spl_autoload_unregister();
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'load'));
    }

    /**
     * Load a class based on a class name
     *
     * @param string  $class    The class name
     * @throws \RuntimeException If debug is enabled and the class could not be found in the file.
     * @return boolean  Returns TRUE if the class could be loaded, otherwise returns FALSE.
     */
    public function load($class)
    {
        $result = false;

        //Get the path
        $path = $this->getPath( $class );

        if ($path !== false)
        {
            if (!in_array($path, get_included_files()) && file_exists($path))
            {
                require $path;

                if($this->_debug)
                {
                    if(!$this->isDeclared($class))
                    {
                        throw new \RuntimeException(sprintf(
                            'The autoloader expected class "%s" to be defined in file "%s".
                            The file was found but the class was not in it, the class name
                            or namespace probably has a typo.', $class, $path
                        ));
                    }
                }

                //Class has been loaded
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Get the path based on a class name
     *
     * @param string $class    The class name
     * @return string|boolean  Returns canonicalized absolute pathname or FALSE of the class could not be found.
     */
    public function getPath($class)
    {
        if(!$this->__registry->has($class))
        {
            if(!$locator = $this->__registry->getLocator($class))
            {
                $locators = $this->getLocators();

                foreach($locators as $locator)
                {
                    if(false !== $path = $locator->locate($class))
                    {
                        $this->__registry->setLocator($class, $locator->getName());
                        break;
                    };
                }
            }
            else $path = $this->getLocator($locator)->locate($class);

            $this->__registry->set($class, $path);

        }
        else $path = $this->__registry->get($class);

        return $path;
    }

    /**
     * Get the path based on a class name
     *
     * @param string $class The class name
     * @param string $path  The class path
     * @return void
     */
    public function setPath($class, $path)
    {
        $this->__registry->set($class, $path);
    }

    /**
     * Register a class locator
     *
     * @param  ClassLocatorInterface $locator
     * @param  bool $prepend If true, the locator will be prepended instead of appended.
     * @return void
     */
    public function registerLocator(ClassLocatorInterface $locator, $prepend = false )
    {
        $array = array($locator->getName() => $locator);

        if($prepend) {
            $this->_locators = $array + $this->_locators;
        } else {
            $this->_locators = $this->_locators + $array;
        }
    }

    /**
     * Get a registered class locator based on his type
     *
     * @param string $type The locator type
     * @return ClassLocatorInterface|null  Returns the object locator or NULL if it cannot be found.
     */
    public function getLocator($type)
    {
        $result = null;

        if(isset($this->_locators[$type])) {
            $result = $this->_locators[$type];
        }

        return $result;
    }

    /**
     * Get the registered class locators
     *
     * @return array
     */
    public function getLocators()
    {
        return $this->_locators;
    }

    /**
     * Register an alias for a class
     *
     * @param string  $class The original
     * @param string  $alias The alias name for the class.
     */
    public function registerAlias($class, $alias)
    {
        $alias = trim($alias);
        $class = trim($class);

        $this->__registry->alias($class, $alias);
    }

    /**
     * Get the registered alias for a class
     *
     * @param  string $class The class
     * @return array   An array of aliases
     */
    public function getAliases($class)
    {
        return array_search($class, $this->__registry->getAliases());
    }

    /**
     * Enable or disable debug
     *
     * If debug is enabled the class loader will throw an exception if a file is found but does not declare the class.
     *
     * @param bool|null $debug True or false.
     * @return ClassLoader
     */
    public function setDebug($debug)
    {
        $this->_debug = (bool) $debug;
        return $this;
    }

    /**
     * Check if the loader is runnign in debug mode
     *
     * @return bool
     */
    public function isDebug()
    {
        return $this->_debug;
    }

    /**
     * Enable or disable the cache
     *
     * @param bool $cache True or false.
     * @param string $namespace The cache namespace
     * @return ClassLoaderInterface
     */
    public function setCache($cache, $namespace = null)
    {
        if($cache && ClassRegistryCache::isSupported())
        {
            $this->__registry = new ClassRegistryCache();

            if($namespace) {
                $this->__registry->setNamespace($namespace);
            }
        }
        else
        {
            if(!$this->__registry instanceof ClassRegistry) {
                $this->__registry = new ClassRegistry();
            }
        }

        return $this;
    }

    /**
     * Check if caching is enabled
     *
     * @return bool
     */
    public function isCache()
    {
        return $this->__registry instanceof ClassRegistryCache;
    }

    /**
     * Tells if a class, interface or trait exists.
     *
     * @param string $class
     * @return boolean
     */
    public function isDeclared($class)
    {
        return class_exists($class, false)
        || interface_exists($class, false)
        || (function_exists('trait_exists') && trait_exists($class, false));
    }
}
