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

class DispatcherBehaviorRoutable extends Library\ControllerBehaviorAbstract
{
    private $__route;

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(array(
            'priority' => self::PRIORITY_HIGH,
        ));

        parent::_initialize($config);
    }

    public function getRoute()
    {
        return clone $this->__route;
    }

    protected function _beforeDispatch(Library\DispatcherContextInterface $context)
    {
        $query = $context->getRequest()->getQuery();

        if ($query->has('component')) {
            $wp_query = \EasyDocLabs\WP::global('wp_query');

            $route = '';

            if (isset($wp_query->query['route'])) {
                $route = $wp_query->query['route'];
            } else if ($query->has('route')) {
                $route = $query->get('route', 'raw');
            }

            $component  = $query->get('component', 'cmd');

            if(false === $route = $context->router->resolve($component.':'.$route, $query->toArray())) {
                throw new Library\HttpExceptionNotFound('Page Not Found');
            }

            //Set the query in the request
            $context->getRequest()->setQuery($route->query);

            if ($view = $context->getRequest()->getQuery()->get('view', 'cmd')) {
                $this->getMixer()->setController($context->getRequest()->getQuery()->get('view', 'cmd'));
            }

            //Store the route
            $this->__route = $route;
        }
    }
}