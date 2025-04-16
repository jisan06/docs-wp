<?php
/**
 * Foliokit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     MPL v2.0 <https://www.mozilla.org/en-US/MPL/2.0>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Library;

/**
 * Routable Dispatcher Behavior
 *
 * Redirects the page to the default view
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Dispatcher\Behavior
 */
class DispatcherBehaviorRoutable extends DispatcherBehaviorAbstract
{
    /**
     * Redirects the page to the default view
     *
     * @param   DispatcherContext $context The active command context
     * @return  bool
     */
    protected function _beforeDispatch(DispatcherContext $context)
    {
        $view = $context->request->query->get('view', 'cmd');

        //Redirect if no view information can be found in the request
        if(empty($view))
        {
            $url = clone($context->request->getUrl());
            $url->query['view'] = $this->getController()->getView()->getName();

            $this->redirect($url);

            return false;
        }

        return true;
    }
}
