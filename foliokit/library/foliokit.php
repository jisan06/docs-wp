<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

/**
 * Foliokit constant, if true Foliokit is loaded
 */
define('FOLIOKIT', 1);

use EasyDocLabs\Library;

/**
 * Foliokit Loader
 *
 * Loads classes and files, and provides metadata for Foliokit such as version info
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library
 *
 * @method static EasyDocLabs\Library\ObjectInterface getObject(mixed $identifier, array $config = array())
 * @method static EasyDocLabs\Library\ObjectIdentifier getIdentifier(mixed $identifier = null)
 */
class Foliokit
{
    /**
     * Foliokit version
     *
     * @var string
     */
    const VERSION = '3.1.3';

    /**
     * Debug state
     *
     * @var boolean
     */
    protected static $_debug;

    /**
     * Cache state
     *
     * @var boolean
     */
    protected static $_cache;

    /**
     * The root path
     *
     * @var string
     */
    protected static $_root_path;

    /**
     * The base path
     *
     * @var string
     */
    protected static $_base_path;

    /**
     * The vendor path
     *
     * @var string
     */
    protected static $_vendor_path;

    /**
     * The object manager
     *
     * @var Library\ObjectManager
     */
    private static $__object_manager;

    /**
     * Constructor
     *
     * Prevent creating instances of this class by making the constructor private
     *
     * @param  array  $config An optional array with configuration options.
     */
    final private function __construct($config = array())
    {
        /**
         * Setup the configuration
         */

        if(isset($config['debug'])) {
            self::$_debug = $config['debug'];
        } else {
            self::$_debug = (getenv('FOLIOKIT_DEBUG') !== false ? filter_var( getenv('FOLIOKIT_DEBUG') , FILTER_VALIDATE_BOOLEAN) : false);
        }

        if(isset($config['cache'])) {
            self::$_cache = $config['cache'];
        } else {
            self::$_cache = false;
        }

        if(isset($config['root_path'])) {
            self::$_root_path = $config['root_path'];
        } else {
            self::$_root_path = realpath($_SERVER['DOCUMENT_ROOT']);
        }

        if(isset($config['base_path'])) {
            self::$_base_path = $config['base_path'];
        } else {
            self::$_base_path = self::$_root_path;
        }

        if(isset($config['vendor_path'])) {
            self::$_vendor_path = $config['vendor_path'];
        } else {
            self::$_vendor_path = self::$_root_path.'/vendor';
        }

        /*
         * Load functions
         */
        require_once dirname(__FILE__) . '/functions.php';

        /**
         * Load the legacy functions
         */
        require_once dirname(__FILE__) . '/legacy.php';

        /**
         * Load the class locator
         */
        require_once dirname(__FILE__) . '/class/loader/loader.php';

        $loader = new Library\ClassLoader();
        $loader->setDebug(self::isDebug());
        $loader->setCache(self::isCache());

        //Register the component class locator
        $loader->registerLocator(new Library\ClassLocatorComponent(), true);

        //Register the extension class locator
        $loader->registerLocator(new Library\ClassLocatorExtension(), true);

        //Register the composer class locator
        if(file_exists($this->getVendorPath())) {
            $loader->registerLocator(new Library\ClassLocatorComposer());
        }

        //Register the PSR locator
        $loader->registerLocator(new Library\ClassLocatorPsr);

        /**
         * Setup the object manager
         */
        $manager = new Library\ObjectManager($config);
        $manager->setCache(self::isCache());
        $manager->setDebug(self::isDebug());
        $manager->setClassLoader($loader);

        //Register the component object locator
        $manager->registerLocator('lib:object.locator.component');

        //Register the extension object locator
        $manager->registerLocator('lib:object.locator.extension');

        //Warm-up the stream factory
        $manager->getObject('lib:filesystem.stream.factory');

        //Store the object manager
        self::$__object_manager = $manager;
    }

    /**
     * Clone
     *
     * Prevent creating clones of this class
     */
    private function __clone() { }

    /**
     * Singleton instance
     *
     * @param  array  $config An optional array with configuration options.
     * @return Foliokit
     */
    final public static function getInstance($config = array())
    {
        static $instance;

        if ($instance === NULL) {
            $instance = new self($config);
        }

        return $instance;
    }

    /**
     * Get the framework version
     *
     * @return string
     */
    public static function getVersion()
    {
        return self::VERSION;
    }

    /**
     * Get vendor path
     *
     * @return string
     */
    public static function getVendorPath()
    {
        return self::$_vendor_path;
    }

    /**
     * Get root path
     *
     * @return string
     */
    public static function getRootPath()
    {
        return self::$_root_path;
    }

    /**
     * Get base path
     *
     * @return string
     */
    public static function getBasePath()
    {
        return self::$_base_path;
    }

    /**
     * Enable or disable debug
     *
     * @param bool $debug True or false.
     * @return Foliokit
     */
    public static function setDebug($debug)
    {
        return self::$_debug = (bool) $debug;
    }

    /**
     * Check if debug is enabled
     *
     * @return bool
     */
    public static function isDebug()
    {
        return self::$_debug;
    }

    /**
     * Enable or disable the cache
     *
     * @param bool $cache True or false.
     * @return Foliokit
     */
    public static function setCache($cache)
    {
        return self::$_cache = (bool) $cache;
    }

    /**
     * Check if caching is enabled
     *
     * @return bool
     */
    public static function isCache()
    {
        return self::$_cache;
    }

    /**
     * Proxy statid method calls to the object manager
     *
     * @param  string     $method    The function name
     * @param  array      $arguments The function arguments
     * @throws \BadMethodCallException  If method is called statically before Foliokit has been instantiated.
     * @return mixed The result of the method
     */
    public static function __callStatic($method, $arguments)
    {
        if(self::$__object_manager instanceof Library\ObjectManager) {
            return self::$__object_manager->$method(...$arguments);
        }
        else throw new \BadMethodCallException('Cannot call method: $s. Foliokit has not been instantiated', $method);
    }
}
