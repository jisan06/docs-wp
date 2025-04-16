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
 * Dispatcher Router Singleton
 *
 * Force the router object to a singleton with identifier alias 'router'.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Dispatcher\Router
 */
class DispatcherRouterComponentSite extends DispatcherRouterAbstract implements Library\ObjectSingleton
{
    public function getRoute($route, array $parameters = array())
    {
        if(!$route instanceof DispatcherRouterRouteInterface && (!$route || !is_scalar($route))) {
            $route = '';
        }

        return parent::getRoute($route, $parameters);
    }

    public function resolve($route, array $parameters = array())
    {
        $route = $this->getRoute($route);

        $route->setQuery($parameters);

        return parent::resolve($route, $parameters);
    }

    public function generate($entity, array $parameters = [])
    {
        $route    = $this->getRoute($entity, $parameters);

        if (isset($parameters['layout']) && $parameters['layout'] === 'default'){
            unset($parameters['layout']);
        }

        $route = parent::generate($route, $parameters);

        return $route;
    }
}