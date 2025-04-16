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
 * Abstract Dispatcher Router
 *
 * Provides route building and parsing functionality
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Dispatcher\Router
 */
interface DispatcherRouterInterface
{
    /**
     * Function to convert a route to an internal URI
     *
     * @param   HttpUrlInterface  $url  The url.
     * @return  boolean
     */
    public function parse(HttpUrlInterface $url);

    /**
     * Function to convert an internal URI to a route
     *
     * @param   HttpUrl   $url The internal URL
     * @return  boolean
     */
    public function build(HttpUrlInterface $url);
}
