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
 * Abstract Dispatcher Router
 *
 * The router add resolvers to a double linked list to allow. The order in which resolvers are called depends on the
 * process, when resolving resolvers are called in FIFO order, when generating the resolvers are called in LIFO order.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Dispatcher\Router
 */
abstract class DispatcherRouterAbstract extends Library\ObjectAbstract implements DispatcherRouterInterface, Library\ObjectMultiton
{
    /**
     * Request object
     *
     * @var	Library\ControllerRequestInterface
     */
    private $__request;

    /**
     * The route resolver stack
     *
     * @var	\SplDoublyLinkedList
     */
    private $__resolvers;

    /**
     * Constructor
     *
     * @param   Library\ObjectConfig $config Configuration options
     */
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->setRequest($config->request);

        $this->setResolvers($config->resolvers);
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   Library\ObjectConfig $config    An optional ObjectConfig object with configuration options.
     * @return 	void
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(array(
            'request'   => null,
            'route'     => 'default',
            'resolvers' => [],
        ));

        parent::_initialize($config);
    }

    /**
     * Resolve a route
     *
     * @param string|DispatcherRouterRouteInterface|Library\ObjectInterface $route The route to resolve
     * @param array $parameters Route parameters
     * @return false| DispatcherRouterRouteInterface Returns the matched route or false if no match was found
     */
    public function resolve($route, array $parameters = array())
    {
        $route = $this->getRoute($route, $parameters);

        if(!$route->isResolved())
        {
            $this->__resolvers->setIteratorMode(\SplDoublyLinkedList::IT_MODE_LIFO | \SplDoublyLinkedList::IT_MODE_KEEP);

            foreach($this->__resolvers as $resolver)
            {
                $resolver->resolve($route, $parameters);

                if ($route->isResolved()) break; // Stop iterating if route gets resolved
            }
        }

        return $route->isResolved() ? $route : false;
    }

    /**
     * Generate a route
     *
     * @param string|DispatcherRouterRouteInterface|Library\ObjectInterface $route The route to resolve
     * @param array $parameters Route parameters
     * @return false|Library\HttpUrlInterface Returns the generated route
     */
    public function generate($route, array $parameters = array())
    {
        $route = $this->getRoute($route, $parameters);

        if(!$route->isGenerated())
        {
            $this->__resolvers->setIteratorMode(\SplDoublyLinkedList::IT_MODE_FIFO | \SplDoublyLinkedList::IT_MODE_KEEP);

            foreach($this->__resolvers as $resolver)
            {
                $resolver->generate($route, $parameters);
                
                if ($route->isGenerated()) break; // Stop iterating if route gets generated
            }
        }

        return $route->isGenerated() ? $route : false;
    }

    /**
     * Qualify a route
     *
     * @param   DispatcherRouterRouteInterface      $route The route to qualify
     * @param   bool  $replace If the url is already qualified replace the authority
     * @return  string
     */
    public function qualify(DispatcherRouterRouteInterface $route, $replace = false)
    {
        $url = clone $route;

        if($replace || !$route->isAbsolute())
        {
            $request = $this->getRequest();

            //Qualify the url
            $url->setUrl($request->getUrl()->toString(Library\HttpUrl::AUTHORITY));

            //Add index.php
            $base = $request->getBasePath();
            $path = trim($url->getPath(), '/');

            if(strpos($request->getUrl()->getPath(), 'index.php') !== false) {
                $url->setPath($base . '/index.php/' . $path);
            } else {
                $url->setPath($base.'/'.$path);
            }
        }
        return $url;
    }

    /**
     * Get a route
     *
     * @param string|DispatcherRouterRouteInterface $route The route to compile
     * @param array $parameters Route parameters
     * @return DispatcherRouterRouteInterface
     */
    public function getRoute($route, array $parameters = array())
    {
        if(!$route instanceof DispatcherRouterRouteInterface)
        {
            $name = $this->getConfig()->route;

            if(is_string($name) && strpos($name, '.') === false )
            {
                $identifier         = $this->getIdentifier()->toArray();
                $identifier['path'] = ['dispatcher', 'router', 'route'];
                $identifier['name'] = $name;

                $identifier = $this->getIdentifier($identifier);
            }
            else $identifier = $this->getIdentifier($name);

            $route = $this->getObject($identifier, ['url' => $route, 'query' => $parameters]);

            if(!$route instanceof DispatcherRouterRouteInterface)
            {
                throw new \UnexpectedValueException(
                    'Route: '.get_class($route).' does not implement DispatcherRouterRouteInterface'
                );
            }
        }
        else
        {
            $route = clone $route;
            $route->setQuery($parameters, true);
        }

        return $route;
    }

    /**
     * Get a route resolver
     *
     * @param   mixed   $resolver  Library\ObjectIdentifier object or valid identifier string
     * @param   array $config  An optional associative array of configuration settings
     * @throws \UnexpectedValueException
     * @return  DispatcherRouterResolverInterface
     */
    public function getResolver($resolver, $config = array())
    {
        if(is_string($resolver) && strpos($resolver, '.') === false )
        {
            $identifier         = $this->getIdentifier()->toArray();
            $identifier['path'] = ['dispatcher', 'router', 'resolver'];
            $identifier['name'] = $resolver;

            $identifier = $this->getIdentifier($identifier);
        }
        else $identifier = $this->getIdentifier($resolver);

        $resolver = $this->getObject($identifier, $config);

        if (!($resolver instanceof DispatcherRouterResolverInterface))
        {
            throw new \UnexpectedValueException(
                "Resolver $identifier does not implement DispatcherRouterResolverInterface"
            );
        }

        return $resolver;
    }

    public function setResolvers($resolvers)
    {
        //Create the resolver queue
        
        $this->__resolvers = new \SplDoublyLinkedList();

        $resolvers = (array) Library\ObjectConfig::unbox($resolvers);

        foreach ($resolvers as $key => $value)
        {
            if (is_numeric($key)) {
                $this->attachResolver($value);
            } else {
                $this->attachResolver($key, $value);
            }
        }
    }

    /**
     * Get the list of attached resolvers
     *
     * @return array
     */
    public function getResolvers()
    {
        return iterator_to_array($this->__resolvers);
    }

    /**
     * Attach a route resolver
     *
     * @param   mixed  $resolver An object that implements ObjectInterface, ObjectIdentifier object
     *                            or valid identifier string
     * @param   array $config  An optional associative array of configuration settings
     * @param  bool $prepend If true, the resolver will be prepended instead of appended.
     * @return  DispatcherRouterAbstract
     */
    public function attachResolver($resolver, $config = array(), $prepend = false)
    {
        if (!($resolver instanceof DispatcherRouterResolverInterface)) {
            $resolver = $this->getResolver($resolver, $config);
        }

        //Enqueue the router resolver
        if($prepend) {
            $this->__resolvers->unshift($resolver);
        } else {
            $this->__resolvers->push($resolver);
        }

        return $this;
    }

    /**
     * Set the request object
     *
     * @param Library\ControllerRequestInterface $request A request object
     * @return DispatcherRouterAbstract
     */
    public function setRequest(Library\ControllerRequestInterface $request)
    {
        $this->__request = $request;
        return $this;
    }

    /**
     * Get the request object
     *
     * @return Library\ControllerRequestInterface
     */
    public function getRequest()
    {
        return $this->__request;
    }

    /**
     * Deep clone of this instance
     *
     * @return void
     */
    public function __clone()
    {
        parent::__clone();

        $this->__request   = clone $this->__request;
        $this->__resolvers = clone $this->__resolvers;
    }
}