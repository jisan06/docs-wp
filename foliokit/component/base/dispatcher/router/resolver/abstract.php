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
 * Abstract Dispatcher Route Resolver
 *
 * Inspired by Altorouter: https://github.com/dannyvankooten/AltoRouter
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Dispatcher\Router\Resolver
 */
abstract class DispatcherRouterResolverAbstract extends Library\ObjectAbstract implements DispatcherRouterResolverInterface
{
    /**
     *  Resolve the route
     *
     * @param DispatcherRouterRouteInterface $route The route to resolve
     * @param array $parameters Route parameters
     * @return bool
     */
    public function resolve(DispatcherRouterRouteInterface $route, array $parameters = [])
    {
        return $route->isResolved();
    }

    /**
     * Reversed routing
     *
     * @param DispatcherRouterRouteInterface $route The route to generate
     * @param array $parameters Route parameters
     * @return bool
     */
    public function generate(DispatcherRouterRouteInterface $route, array $parameters = [])
    {
        $route->setGenerated();

        return true;
    }
}