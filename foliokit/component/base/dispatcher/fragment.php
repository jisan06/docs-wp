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
 * Dispatcher
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class DispatcherFragment extends Library\DispatcherFragment
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

        $this->__router  = $config->router;

        $this->addCommandCallback('before.include', '_setMessages');
        $this->addCommandCallback('before.dispatch', '_setMessages');
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
        $config->append([
            'router'  => 'com:base.dispatcher.router.site',
            'behaviors'         => [
                'com:base.dispatcher.behavior.routable',
            ]
        ]);

        parent::_initialize($config);
    }

    /**
     * Resolve the request
     *
     * @param DispatcherContext $context A dispatcher context object
     */
    protected function _resolveRequest(Library\DispatcherContext $context)
    {
        if (!$this->_controller) {
            parent::_resolveRequest($context);
        }
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

    protected function _setMessages(Library\DispatcherContext $context)
    {
        $session = $this->getUser()->getSession();

        //Set the response messages on the main response object

        $this->getObject('response')->setMessages($session->getContainer('message')->all());
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
}
