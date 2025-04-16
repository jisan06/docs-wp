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
use EasyDocLabs\WP;

class DispatcherRouterResolverSite extends DispatcherRouterResolverAbstract implements Library\ObjectMultiton
{
    /**
     * @var array
     */
    protected $_defaults = [];

    /**
     * Set router defaults (current page query)
     *
     * @param $defaults
     * @return $this
     */
    public function setDefaults($defaults)
    {
        $this->_defaults = $defaults;

        return $this;
    }

    /**
     * Get router defaults
     *
     * @return array
     */
    public function getDefaults()
    {
        return $this->_defaults;
    }

    /**
     *  Resolve the route
     *
     * @param DispatcherRouterRouteInterface $route The route to resolve
     * @return bool
     */
    public function resolve(DispatcherRouterRouteInterface $route, array $parameters = [])
    {
        $controller = isset($parameters['controller']) ? $parameters['controller'] : '';
        $component  = isset($parameters['component']) ? $parameters['component'] : '';

        if ($component == 'foliokit' && $controller == 'license') {
            $route->setResolved(); // Let the request to go through
        }

        return parent::resolve($route);
    }

    public function generate(DispatcherRouterRouteInterface $route, array $parameters = [])
    {
        $query = $route->getQuery(true);

        if (isset($query['layout']) && $query['layout'] === 'default')
        {
            unset($query['layout']);
            
            $route->setQuery($query); // Update route
        }

        $request = $this->getObject('request');
		$current = WP::get_post();

        if (!get_option('permalink_structure') || (isset($current) && $current->post_status == 'draft'))
        {
            // Add the 'page' information to the route if a 'page' is set in the request
            if (!isset($query['endpoint']))
            {
                if (!isset($query['page_id']) && $page = $request->getQuery()->get('page_id', 'cmd')) {
                    $route->setQuery(['page_id' => $page], true);
                } else if ($endpoint = $request->getQuery()->get('endpoint', 'string')) {
                    $route->setQuery(['endpoint' => $endpoint], true);
                }
            }

            // Add the route as a query string parameter
            $route->setQuery(['route' => $route->getPath()], true);
            $route->setPath('');
        }
        else if (!isset($query['endpoint']) && $endpoint = $request->getQuery()->get('endpoint', 'string'))
        {
             $route->setQuery(['endpoint' => $endpoint], true);
        }

        if ($defaults = $this->getDefaults())
        {
            $query = $route->getQuery(true);
            $route->setQuery(array_diff_assoc($query, $defaults));
        }

        parent::generate($route);
    }
}
