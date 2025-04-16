<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/nooku/nooku-framework-wordpress for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

/**
 * Dispatcher Route Resolver Interface
 *
 * Provides route building and parsing functionality
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Dispatcher\Router\Resolver
 */
interface DispatcherRouterResolverInterface
{
    /**
     *  Resolve the route
     *
     * @param DispatcherRouterRouteInterface $route The route to resolve
     * @param array $parameters Route parameters
     * @return bool
     */
    public function resolve(DispatcherRouterRouteInterface $route, array $parameters = []);

    /**
     * Reversed routing
     *
     * @param DispatcherRouterRouteInterface $route The route to generate
     * @param array $parameters Route parameters
     * @return bool
     */
    public function generate(DispatcherRouterRouteInterface $route, array $parameters = []);
}
