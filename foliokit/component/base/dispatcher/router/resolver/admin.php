<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/nooku/nooku-framework-wordpress for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

class DispatcherRouterResolverAdmin extends DispatcherRouterResolverAbstract
{
    public function resolve(DispatcherRouterRouteInterface $route, array $parameters = [])
    {
        $route->setResolved();

        return parent::resolve($route, $parameters = []);
    }

    public function generate(DispatcherRouterRouteInterface $route, array $parameters = [])
    {
        // Add the 'page' information to the route if a 'page' is set in the request
        $query = $route->getQuery(true);

        if (!isset($query['page']) && $page = $this->getObject('request')->getQuery()->get('page', 'cmd')) {
            $route->setQuery(['page' => $page], true);
        }

        return parent::generate($route, $parameters = []);
    }
}