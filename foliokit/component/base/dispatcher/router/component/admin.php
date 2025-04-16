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
class DispatcherRouterComponentAdmin extends DispatcherRouterAbstract implements Library\ObjectSingleton
{
    public function getRoute($route, array $parameters = array())
    {
        if (is_string($route) && empty($parameters)) {
            @parse_str($route, $result);

            if (!empty($result)) {
                $route = '';
                $parameters = $result;
            }
        }

        if (!$route || !is_scalar($route)) {
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

        $parameters['component'] = $this->getIdentifier()->getPackage();

        if ($entity instanceof Library\ModelEntityRowset && count($entity) === 1) {
            $entity = $entity->getIterator()->current();
        }

        if ($entity instanceof Library\ModelEntityInterface) {
            if (!isset($parameters['view'])) {
                $parameters['view'] = $entity->getIdentifier()->getName();
            }

            $parameters['id'] = $entity->id;
        }

        if (isset($parameters['layout']) && $parameters['layout'] === 'default'){
            unset($parameters['layout']);
        }

        $route->setQuery($parameters);

        $route = parent::generate($route, $parameters);

        return $route;
    }
}