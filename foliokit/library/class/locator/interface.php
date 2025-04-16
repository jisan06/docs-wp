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
 * Loader Adapter Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Class\Locator
 */
interface ClassLocatorInterface
{
    /**
     * Get locator name
     *
     * @return string
     */
    public static function getName();

    /**
     * Get a fully qualified path based on a class name
     *
     * @param  string  $class    The class name
     * @return string|boolean    Returns the path on success FALSE on failure
     */
    public function locate($class);

    /**
     * Register a namespace
     *
     * @param  string       $namespace
     * @param  string|array $path(s) The location of the namespace
     * @return ClassLocatorInterface
     */
    public function registerNamespace($namespace, $path);

    /**
     * Get a namespace path(s)
     *
     * @param string $namespace The namespace
     * @return array|false The namespace path(s) or FALSE if the namespace does not exist.
     */
    public function getNamespacePaths($namespace);

    /**
     * Get the registered namespaces
     *
     * @return array An array with namespaces as keys and path as value
     */
    public function getNamespaces();
}
