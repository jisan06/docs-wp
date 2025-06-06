<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/nooku/nooku-framework-wordpress for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

class DispatcherRouterResolverPagination extends DispatcherRouterResolverAbstract
{
    public function resolve(DispatcherRouterRouteInterface $route, array $parameters = [])
    {
        $state = $route->getState();

        if($route->getFormat() == 'json')
        {
            if(isset($route->query['page']))
            {
                $page  = $route->query['page'];

                if(isset($page['number']) && $state['limit']) {
                    $route->query['offset'] = ($page['number'] - 1) * $state['limit'];
                }

                if(isset($page['limit'])) {
                    $route->query['limit'] = $page['limit'];
                }

                if(isset($page['offset'])) {
                    $route->query['offset'] = $page['offset'];
                }

                if(isset($page['total'])) {
                    $route->query['total'] = $page['total'];
                }

                unset($route->query['page']);
            }
        }
        else
        {
            if(isset($route->query['page']))
            {
                $page = $route->query['page'];

                if($page && $state['limit']) {
                    $route->query['offset'] = ($page - 1) * $state['limit'];
                }

                unset($route->query['page']);
            }
        }
    }

    public function generate(DispatcherRouterRouteInterface $route, array $parameters = [])
    {
        $state = $route->getState();
        $page = array();

        if($route->getFormat() == 'json')
        {
            if (isset($route->query['offset'])) {
                $page['offset'] = $route->query['offset'];
                unset($route->query['offset']);
            }

            if (isset($route->query['limit'])) {
                $page['limit'] = $route->query['limit'];
                unset($route->query['offset']);
            }

            if (isset($route->query['total'])) {
                $page['total'] = $route->query['total'];
                unset($route->query['total']);
            }

            if (isset($state['limit']) && isset($page['offset']))
            {
                $page['number'] = ceil($page['offset'] / $state['limit']) + 1;
                unset($page['offset']);
            }

            $route->query['page'] = $page;
        }
        else
        {
            if (isset($state['limit']) && isset($route->query['offset']))
            {
                $page = ceil($route->query['offset'] / $state['limit']) + 1;

                if($page > 1) {
                    $route->query['page'] = $page;
                }

                unset($route->query['offset']);
            }
        }
    }
}