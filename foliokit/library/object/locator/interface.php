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
 * Object Locator Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Object\Locator
 */
interface ObjectLocatorInterface
{
    /**
     * Get the type
     *
     * @return string
     */
    public static function getType();

    /**
     * Returns a fully qualified class name for a given identifier.
     *
     * @param ObjectIdentifier $identifier An identifier object
     * @param bool  $fallback   Use the fallback sequence to locate the identifier
     * @return string|false  Return the class name on success, returns FALSE on failure
     */
    public function locate(ObjectIdentifier $identifier, $fallback = true);

    /**
     * Parse the identifier
     *
     * @param  ObjectIdentifier $identifier An object identifier
     * @return array
     */
    public function parseIdentifier(ObjectIdentifier $identifier);

    /**
     * Get the list of class templates for an identifier
     *
     * @param ObjectIdentifier $identifier The object identifier
     * @return array The class templates for the identifier
     */
    public function getClassTemplates(ObjectIdentifier $identifier);

    /**
     * Register an identifier
     *
     * @param  string       $identifier
     * @param  string|array $namespace(s) Sequence of fallback namespaces
     * @return ObjectLocatorAbstract
     */
    public function registerIdentifier($identifier, $namespaces);

    /**
     * Get the namespace(s) for the identifier
     *
     * @param string $identifier The package identifier
     * @return string|false The namespace(s) or FALSE if the identifier does not exist.
     */
    public function getIdentifierNamespaces($identifier);
}
