<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Library;

/**
 * Html View
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Base
 */
class ViewBehaviorRoutable extends Library\ViewBehaviorAbstract
{
    protected function _beforeRender(Library\ViewContextInterface $context)
    {
        if($context->subject instanceof Library\ViewTemplatable)
        {
            $context->subject
                ->getTemplate()
                ->registerFunction('route', array($this, 'getRoute'));
        }
    }

    public function getRoute($entity = null, $query = [], $escape = null)
    {
        if (is_bool($query)) {
            $query = [];
        }

        if ($query === [] && (is_array($entity) || is_string($entity))) {
            $query = $entity;
            $entity = '';
        }

        if(is_string($query)) {
            $parts = array();
            parse_str(trim($query), $parts);
            $query = $parts;
        }

        $mixer      = $this->getMixer();
        $identifier = $mixer->getIdentifier();

        //Check to see if there is component information in the route if not add it
        if (!isset($query['component'])) {
            $query['component'] = $identifier->package;
        }

        //Add the view information to the route if it's not set
        if (!isset($query['view'])) {
            $query['view'] = $this->getMixer()->getName();
        }

        //Add the format information to the route only if it's not 'html'
        if (!isset($query['format']) && $identifier->name !== 'html') {
            $query['format'] = $identifier->name;
        }

        if (!$entity instanceof Library\ModelEntityInterface)
        {
            //Add the model state and layout only for routes to the same view
            if ($query['component'] == $identifier->package && $query['view'] == $this->getMixer()->getName())
            {
                $unique_states = [];

                // Get unique states
                foreach ($this->getModel()->getState() as $name => $state) {
                    if ($state->unique) $unique_states[] = $name;
                }

                $unique_query = !!count(array_intersect(array_keys($query), $unique_states));
                $merge        = [];

                foreach ($this->getModel()->getState() as $name => $state)
                {
                    if ($state->default != $state->value && !$state->internal && !($state->unique && $unique_query)) {
                        $merge[$name] = $state->value;
                    }
                }

                $query = array_merge($merge, $query);
            }
        }

        //Add the layout information
        if(!isset($query['layout']) && $this->getMixer() instanceof Library\ViewTemplatable)
        {
            $layout = $this->getLayout();

            $query['layout'] = $layout;

        }

        $context = $mixer->getContext();

        if ($entity instanceof Library\ModelEntityInterface) {
            $context->setEntity($entity);
        }

        $context->query = $query;

        $mixer->invokeCommand('before.routing', $context);

        $query = Library\ObjectConfig::unbox($context->query);

        $router = $this->getObject('router');
        $route  = $router->generate($entity, $query);

        if ($route)
		{
            $route->setEscaped($escape);
            $result = $route->toString();
        }
        else $result = $this->getObject('dispatcher')->getRoute();

        return $result;
    }
}
