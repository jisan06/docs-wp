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
 * Locator Filesystem Interface
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Filesystem\Locator
 */
interface FilesystemLocatorInterface
{
    /**
     * Get the locator name
     *
     * @return string The locator name
     */
    public static function getName();

    /**
     * Locate the translation based on a physical path
     *
     * @param  string $url       The resource url
     * @return string|false  The physical file path for the resource or FALSE if the url cannot be located
     */
    public function locate($url);

    /**
     * Parse the resource url
     *
     * @param  string $url The resource url
     * @return array
     */
    public function parseUrl($url);

    /**
     * Register a path template
     *
     * @param  string $template   The path template
     * @param  bool $prepend      If true, the template will be prepended instead of appended.
     * @return FilesystemLocatorInterface
     */
    public function registerPathTemplate($template, $prepend = false);

    /**
     * Get the list of path templates
     *
     * @param  string $url   The resource url
     * @return array The path templates
     */
    public function getPathTemplates($url);

    /**
     * Get a path from an file
     *
     * Function will check if the path is an alias and return the real file path
     *
     * @param  string $file The file path
     * @return string The real file path
     */
    public function realPath($file);

    /**
     * Returns true if the resource is still fresh.
     *
     * @param  string $url    The resource url
     * @param int     $time   The last modification time of the cached resource (timestamp)
     * @return bool TRUE if the resource is still fresh, FALSE otherwise
     */
    public function isFresh($url, $time);
}
