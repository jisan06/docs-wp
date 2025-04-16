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
 * Composer Class Locator
 *
 * Proxy calls to the Composer Autoloader through Composer\Autoload\ClassLoader::findFile().
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Class\Locator
 */
class ClassLocatorComposer extends ClassLocatorAbstract
{
    /**
     * The locator name
     *
     * @var string
     */
    protected static $_name = 'composer';

    /**
     * The composer loader
     *
     * @var \Composer\Autoload\ClassLoader
     */
    private static $__loader = null;

    /**
     * Constructor
     *
     * @param array $config Array of configuration options.
     */
    public function __construct($config = array())
    {
        if(isset($config['vendor_path'])) {
            $vendor_path = $config['vendor_path'];
        } else {
            $vendor_path = \Foliokit::getVendorPath();
        }

        if(file_exists($vendor_path.'/autoload.php')) {
            self::$__loader = require $vendor_path.'/autoload.php';
        } else {
            throw new \RuntimeException(sprintf('Vendor_path: %s does not exst', $vendor_path));
        }
    }

    /**
     * Get a fully qualified path based on a class name
     *
     * @param  string $class     The class name
     * @return string|false Returns canonicalized absolute pathname or FALSE of the class could not be found.
     */
    public function locate($class)
    {
        $path = false;

        if(self::$__loader) {
            $path = self::$__loader->findFile($class);
        }

        return $path;
    }

    /**
     * Get the composer class loader
     *
     * @return \Composer\Autoload\ClassLoader|mixed
     */
    public function getLoader()
    {
        return self::$__loader;
    }
}
