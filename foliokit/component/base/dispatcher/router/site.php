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

/**
 * Dispatcher Router Singleton
 *
 * Force the router object to a singleton with identifier alias 'router'.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Dispatcher\Router
 */
class DispatcherRouterSite extends DispatcherRouterBase implements Library\ObjectSingleton
{
    private $__site_folder;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        if (!is_admin()) {
            $this->getObject('manager')->registerAlias($this->getIdentifier(), 'router');
        }

        $basepath = trim($this->getObject('request')->getBasePath(), '/');

        if(WP::is_admin())
        {
            // Running from admin interface, remove trailing wp-admin if found

            $parts = explode('/', $basepath);

            if (end($parts) == 'wp-admin')
            {
                array_pop($parts);
                $basepath = implode('/', $parts);
            }
        }

        $this->__site_folder = $basepath;

    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'resolvers' => [
                'site',
            ]
        ]);

        parent::_initialize($config);
    }

    protected function _getPage()
    {
        $wp_query = WP::global('wp_query');

        if (isset($wp_query->query['pagename'])) {
            return WP::get_page_by_path($wp_query->query['pagename']);
        }

        return null;
    }

    protected function _getBasePath($query)
    {
        $basepath = [];

        if ($this->__site_folder) {
            $basepath[] = $this->__site_folder;
        }

        if (($permalink = WP::get_option('permalink_structure')) && isset($query['endpoint']))
        {
            if (strpos($permalink, '/index.php') === 0) {
                $basepath[] = 'index.php';
            }

            $basepath[] = $query['endpoint'];
        }
        else if ($page = $this->_getPage()) $basepath[] = trim(str_replace(WP::get_home_url(), '', WP::get_permalink($page->ID)), '/');
            
        return implode('/', $basepath);
    }

        /**
     * Qualify a route
     *
     * @param   DispatcherRouterRouteInterface      $route The route to qualify
     * @param   bool  $replace If the url is already qualified replace the authority
     * @return  DispatcherRouterRouteInterface
     */
    public function qualify(DispatcherRouterRouteInterface $route, $replace = false)
    {
        $route = parent::qualify($route, $replace);

        $path = $route->getPath(true);

        // Make sure we always get a trailing slash (/) ... otherwise WP issues problematic re-directs

        if ($structure = \EasyDocLabs\WP::get_option('permalink_structure'))
        {
            $structure = rtrim($structure);

            if (substr($structure, strlen($structure) - 1) == '/') {
                $path[] = '';
            }
        }
        elseif ($path && end($path) !== '') $path[] = '';

        $route->setPath($path);

        return $route;
    }
}