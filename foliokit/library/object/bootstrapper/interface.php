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
 * Object Bootstrapper Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Object\Bootstrapper
 */
interface ObjectBootstrapperInterface extends ObjectHandlable
{
    /**
     * Priority levels
     */
    const PRIORITY_HIGHEST = 1;
    const PRIORITY_HIGH    = 2;
    const PRIORITY_NORMAL  = 3;
    const PRIORITY_LOW     = 4;
    const PRIORITY_LOWEST  = 5;

    /**
     * Perform the bootstrapping
     *
     * @throws \RuntimeException  If the component has already been registered
     * @throws \RuntimeException  If the parent component cannot be found
     * @return void
     */
    public function bootstrap();

    /**
     * Register components from a directory to be bootstrapped
     *
     * All the first level directories are assumed to be component folders and will be registered.
     *
     * @param string  $directory
     * @param bool    $bootstrap If TRUE bootstrap all the components in the directory. Default TRUE
     * @return ObjectBootstrapper
     */
    public function registerComponents($directory, $bootstrap = true);

    /**
     * Register a component to be bootstrapped.
     *
     * Class and object locators will be setup based on the 'bootstrap' information in the composer.json file.
     * If the component contains a /resources/config/bootstrapper.php file it will be registered.
     *
     * @param string $path          The component path
     * @param bool   $bootstrap     If TRUE bootstrap all the components in the directory. Default TRUE
     * @param array  $paths         Additional array of paths
     * @return ObjectBootstrapper
     */
    public function registerComponent($path, $bootstrap = true, array $paths = array());

    /**
     * Register a configuration file to be bootstrapped
     *
     * @param string $path  The absolute path to the file
     * @return ObjectBootstrapperInterface
     */
    public function registerFile($path);

    /**
     * Get the registered components
     *
     * @return array
     */
    public function getComponents();

    /**
     * Get a registered component path
     *
     * @param string $name    The component name
     * @param string $domain  The component domain. Domain is optional and can be NULL
     * @return string Returns the component path if the component is registered. FALSE otherwise
     */
    public function getComponentPaths($name, $domain = null);

    /**
     * Get a hash based on a name and domain
     *
     * @param string $name    The component name
     * @param string $domain  The component domain. Domain is optional and can be NULL
     * @return string The hash
     */
    public function getComponentIdentifier($name, $domain = null);

    /**
     * Get manifest for a registered component
     *
     * @link https://en.wikipedia.org/wiki/Manifest_file
     *
     *
     * @param string $name    The component name
     * @param string $domain  The component domain. Domain is optional and can be NULL
     * @return ObjectConfigJson|false Returns the component info or FALSE if the component couldn't be found.
     */
    public function getComponentManifest($name, $domain = null);

    /**
     * Check if the bootstrapper has been run
     *
     * If you specify a specific component name the function will check if this component was bootstrapped.
     *
     * @param string $name    The component name
     * @param string $domain  The component domain. Domain is optional and can be NULL
     * @return bool TRUE if the bootstrapping has run FALSE otherwise
     */
    public function isBootstrapped($name = null, $domain = null);
}