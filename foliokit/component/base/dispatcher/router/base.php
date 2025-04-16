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
 * Todo: Move this into Base\DispatcherRouterAbstract when router is moved into framework
 *
 * Force the router object to a singleton with identifier alias 'router'.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Dispatcher\Router
 */
class DispatcherRouterBase extends DispatcherRouterAbstract implements Library\ObjectSingleton
{
    /**
     * The routers
     *
     * @var	array
     */
    private $__routers;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        //Add a global object alias
        $this->getObject('manager')->registerAlias($this->getIdentifier(), 'router.'.$this->getIdentifier()->getName());
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'resolvers' => [
            ]
        ]);

        parent::_initialize($config);
    }

    /**
     * Resolve a route
     *
     * Proxy to the specific component router
     *
     * @param string|DispatcherRouterRouteInterface|Library\ObjectInterface $route The route to resolve
     * @param array $parameters Route parameters
     * @return false| DispatcherRouterInterface Returns the matched route or false if no match was found
     */
    public function resolve($route, array $parameters = array())
    {
        //Find router package
        $package = $this->_findPackage($route, $parameters);

        //Get router instance
        $router = $this->_createRouter($package);

        return $router->resolve($route, $parameters);
    }

    /**
     * Generate a route
     *
     *  Proxy to the specific component router
     *
     * @param string|DispatcherRouterRouteInterface|Library\ObjectInterface $route The route to resolve
     * @param array $parameters Route parameters
     * @return false|Library\HttpUrlInterface|DispatcherRouterRouteInterface Returns the generated route
     */
    public function generate($route, array $parameters = array())
    {
        //Find router package
        $package = $this->_findPackage($route, $parameters);

        //Get router instance
        $router = $this->_createRouter($package);

        $route = $router->generate($route, $parameters);

        return $this->qualify($route);
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
        if($replace || !$route->isAbsolute())
        {
            $request = $this->getRequest();

            //Qualify the url
            $route->setUrl($request->getUrl()->toString(Library\HttpUrl::AUTHORITY));

            $path = in_array($route->getPath(), ['/', '']) ? [] : $route->getPath(true);
            $query = $route->getQuery(true);

            $base = $this->_getBasePath($query);

            if (\EasyDocLabs\WP::get_option('permalink_structure') && isset($query['endpoint']))
            {
                unset($query['endpoint']);

                $route->setQuery($query);
            }

            if ($base)
            {
                if (is_string($base))
                {
                    if ($base !== '/')
                    {
                        $base = trim($base, '/');
                        $base = explode('/', $base);
                    }
                    else $base = [];
                }

                $path = array_merge($base, $path);
            }

            $route->setPath($path);
        }

        return $route;
    }

    /**
     * @param string $package
     * @return DispatcherRouterInterface
     */
    protected function _createRouter($package)
    {
        if (!isset($this->__routers[$package])) {
            $domain = $this->getIdentifier()->getName();
            if ($this->getObject('object.bootstrapper')->isBootstrapped($package, $domain)) {
                $identifier = 'com://'.$domain.'/' . $package . '.dispatcher.router';
            } else {
                $identifier = 'com:base.dispatcher.router.component.'.$domain;
            }

            $config = [
                'request'   => $this->getRequest()
            ];

            try {
                $router = $this->getObject($identifier, $config);
            }
            catch (Library\ObjectExceptionNotFound $e) {
                $router = $this->getObject('com:base.dispatcher.router.component.'.$domain, $config);
            }

            foreach ($this->getResolvers() as $resolver) {
                $router->attachResolver($resolver);
            }

            $this->__routers[$package] = $router;
        } else $router = $this->__routers[$package];

        return $router;
    }

    /**
     * @param $route
     * @param   array $parameters  An optional associative array of configuration settings
     * @return mixed|string|null
     */
    protected function _findPackage($route, $parameters = [])
    {
        if ($route instanceof Library\ObjectInterface) {
            if ($route instanceof DispatcherRouterRouteInterface) {
                $package = $route->getScheme();
            } else {
                $package = $route->getIdentifier()->getPackage();
            }
        } else $package = parse_url($route ?: '', PHP_URL_SCHEME);

        if (!$package && isset($parameters['component'])) {
            $package = $parameters['component'];
        }

        return $package;
    }

    protected function _getBasePath($query)
    {
        return $this->getObject('request')->getBasePath();
    }
}