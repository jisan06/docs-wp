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
 * Renders response contents in the post by replacing the block/shortcode
 *
 * @author  Ercan Ozkaya <http://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Base
 */
abstract class BlockFragment extends BlockAbstract
{
    protected $_dispatcher;

    protected $_controller;

    protected $_endpoint;

    protected $_parameters;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_dispatcher = $config->dispatcher;
        $this->_controller = $config->controller;
        $this->_endpoint   = $config->endpoint;
        $this->_parameters = $config->parameters;
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'dispatcher' => 'com:base.dispatcher.fragment',
            'controller' => '',
            'endpoint'   => sprintf('component-%s', $config->object_identifier->getPackage()),
            'parameters' => ['sort', 'direction', 'limit', 'offset']
        ]);

        parent::_initialize($config);
    }

    public function renderFragment($context)
    {
        $controller = $this->getObject($this->_controller, ['request' => $this->getObject('com:base.controller.request')]);
        
        $request = $this->getObject('request');

        if (in_array($request->getFormat(), $controller->getFormats()))
        {
            $this->setController($controller, $context);

            $content = $this->getDispatcher($context)->setController($controller)->include();
    
            $output = $this->getObject('com:base.view.document.html')
                ->setContent($content)
                ->layout('wordpress')
                ->render();
        }
        else
        {
            $context->merge([
                'generic' => true,
                'message' => ['title' => 'Format not supported', 'description' => 'This block cannot be rendered, unsopported format: ' . $request->getFormat(), 'type' => 'danger']
            ]);

            $output = parent::render($context);
        }

        return $output;
    }

    /**
     * Push generic query params from current request to the controller
     *
     * @param Library\ControllerInterface $controller
     * @param [type] $context
     * @return void
     */
    protected function _setQueryParameters(Library\ControllerInterface $controller, $context)
    {
        $request = $controller->getRequest();

        $ignore = $context->_ignore_parameters ?? [];

        $query = $this->getObject('request')->getQuery();

        foreach ($this->_parameters as $parameter => $name)
        {
            if (is_numeric($parameter)) $parameter = $name;

            if (!in_array($parameter, Library\ObjectConfig::unbox($ignore))) {
                if (isset($query->{$parameter})) $request->getQuery()->{$name} = $query->{$parameter};
            }
        }
    }

    public function setController(Library\ControllerInterface $controller, $context)
    {
        if (!$controller->isOptionable()) {
            $controller->addBehavior('optionable');
        }

        $request = $controller->getRequest();

        foreach ($this->getAttributes() as $name => $settings)
        {
            if (isset($settings['request'])) {
                $request->getQuery()->set($settings['request'], Library\ObjectConfig::unbox($context->attributes->{$name}), true);
            }
        }

        $this->_setQueryParameters($controller, $context);

        $controller->setOptionable(['options' => $context->attributes, 'blocks' => $this]);

        if ($controller instanceof Library\ControllerViewable)
        {
            $view = $controller->getView();

            if ($view->isRoutable()) {
                $view->addBehavior('com:base.view.behavior.fragment', ['endpoint' => $this->_endpoint]);
            }
        }

		$user = $this->getObject('user');

		$controller->setUser($user); // Set the current user for the controller request

		// Set Access for permissios checks

		if (!$user->isAdmin()) {
			$request->getQuery()->access = $user->getId();
		}

		if ($controller instanceof Library\ControllerModel) {
            $controller->getModel()->setState($controller->getRequest()->getQuery()->toArray()); // Ensure everything is set
        }

        $this->_controller = $controller;

        return $this;
    }

    public function getDispatcher($context)
    {
        if (!$this->_dispatcher instanceof Library\DispatcherInterface) {
            $this->setDispatcher($this->getObject($this->_dispatcher), $context);
        }

        return $this->_dispatcher;
    }

    public function setDispatcher(Library\DispatcherInterface $dispatcher, $context)
    {
        $this->_dispatcher = $dispatcher;

        return $this;
    }

    public function render($context)
    {
        $output = '';

		if (!$context->generic)
		{
			foreach ($this->getAttributes() as $name => $settings)
			{
				if ($context->attributes->$name === null && isset($settings['default'])) {
					$context->attributes->$name = $settings['default'];
				}
			}

			if ($this->_canRender($context)) {
				$output = $this->renderFragment($context);
			}
		}
		else $output = parent::render($context);

        return $output;
    }

    protected function _canRender($context)
    {
        // REST_REQUEST is true for Gutenberg editor's render requests

        return $this->getObject('request')->isGet() && !\EasyDocLabs\WP::is_admin() &&
               (!defined('REST_REQUEST') || !REST_REQUEST);
    }
}
