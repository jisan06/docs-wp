<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/nooku/nooku-framework-wordpress for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Library;
/**
 * Dispatcher Router Route Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Dispatcher\Router\Route
 */
interface DispatcherRouterRouteInterface extends Library\HttpUrlInterface
{
    const STATUS_RESOLVED  = 1;
    const STATUS_GENERATED = 2;

    /**
     * Get the route state
     *
     * @return array
     */
    public function getState();

    /**
     * Get the format
     *
     * @return string
     */
    public function getFormat();

    /**
     * Mark the route as resolved
     *
     * @return DispatcherRouterRouteInterface
     */
    public function setResolved();

    /**
     * Mark the route as generated
     *
     * @return DispatcherRouterRouteInterface
     */
    public function setGenerated();

    /**
     * Test if the route has been resolved
     *
     * @return	bool
     */
    public function isResolved();

    /**
     * Test if the route has been generated
     *
     * @return	bool
     */
    public function isGenerated();

    /**
     * Test if the route is absolute
     *
     * @return	bool
     */
    public function isAbsolute();
}
