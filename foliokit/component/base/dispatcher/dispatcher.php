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
 * Dispatcher
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class Dispatcher extends Library\Dispatcher
{
    private $__router;

    /**
     * Constructor.
     *
     * @param Library\ObjectConfig $config	An optional KObjectConfig object with configuration options.
     */
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->__router = $config->router;
      }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   Library\ObjectConfig $config Configuration options.
     * @return  void
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $wp_query = WP::global('wp_query');

        $config->append([
            'router'  => 'com:base.dispatcher.router.'.$this->getIdentifier()->getDomain(),
            'behaviors'         => [
                'com:base.dispatcher.behavior.routable',
                'decoratable',
                'limitable' => [
                    'default' => isset($wp_query->query_vars['posts_per_page']) ? $wp_query->query_vars['posts_per_page'] : 20,
                    'max'     => 100
                ]
            ],
        ]);

        parent::_initialize($config);
    }

    public function setRouter(DispatcherRouterInterface $router)
    {
        $this->__router = $router;
        return $this;
    }

    public function getRouter()
    {
        if(!$this->__router instanceof DispatcherRouterInterface)
        {
            $this->__router = $this->getObject($this->__router, array(
                'request' => $this->getRequest(),
            ));

            if(!$this->__router instanceof DispatcherRouterInterface)
            {
                throw new \UnexpectedValueException(
                    'Router: '.get_class($this->__router).' does not implement DispatcherRouterInterface'
                );
            }
        }

        return $this->__router;
    }

    public function getContext(Library\ControllerContextInterface $context = null)
    {
        $context = new DispatcherContext();
        $context->setSubject($this);
        $context->setRequest($this->getRequest());
        $context->setResponse($this->getResponse());
        $context->setUser($this->getUser());
        $context->setRouter($this->getRouter());

        return $context;
    }

    /**
     * Dispatch the request
     *
     * Dispatch to a controller internally. Functions makes an internal sub-request, based on the information in
     * the request and passing along the context.
     *
     * @param Library\DispatcherContext $context  A dispatcher context object
     * @throws  Library\DispatcherExceptionMethodNotAllowed  If the method is not allowed on the resource.
     * @return  mixed
     */
    protected function _actionDispatch(Library\DispatcherContext $context)
    {
        $session = $this->getUser()->getSession();

        //Set the response messages
        $context->response->setMessages($session->getContainer('message')->all());

        return parent::_actionDispatch($context);
    }
}
