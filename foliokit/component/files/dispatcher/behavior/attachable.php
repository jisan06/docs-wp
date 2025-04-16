<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Files;

use EasyDocLabs\Library;

/**
 * Attachable Dispatcher Behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class DispatcherBehaviorAttachable extends Library\BehaviorAbstract
{
    /**
     * The attachments container slug.
     *
     * @var string
     */
    protected $_container;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_container = $config->container;
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'container' => sprintf('%s-attachments',
                $config->mixer->getIdentifier()->getPackage())
        ]);

        parent::_initialize($config);
    }

    /**
     * Before Dispatch command handler.
     *
     * Makes sure to forward requests to com_files or set container data to the request depending on the view.
     *
     * @param Library\DispatcherContextInterface $context The context object.
     *
     * @return bool True if the request should be dispatched, false otherwise.
     */
    protected function _beforeDispatch(Library\DispatcherContextInterface $context)
    {
        $result = true;

        $this->_setAliases();

        $query = $context->getRequest()->getQuery();

        if ($query->routed && in_array($query->view, ['file', 'files', 'node', 'nodes']))
        {
            $this->_forward($context);
            $this->send($context);
            $result = false;
        }
        elseif (in_array($query->view, ['attachment', 'attachments']))
        {
            $container = $this->getObject('com:files.model.containers')->slug($this->_container)->fetch();

            if (!$container->isNew()) {
                $context->getRequest()->getQuery()->container = $container->id;
            }
        }

        return $result;
    }

    /**
     * Alias setter.
     */
    protected function _setAliases()
    {
        $mixer = $this->getMixer();

        $aliases = [
            'com:files.controller.permission.attachment' => [
                'path' => ['controller', 'permission'],
                'name' => 'attachment'
            ],
            'com:files.controller.behavior.attachment'   => [
                'path' => ['controller', 'behavior'],
                'name' => 'attachment'
            ],
            'com:files.controller.attachment'            => [
                'path' => ['controller'],
                'name' => 'attachment'
            ]
        ];

        $manager = $this->getObject('manager');

        foreach ($aliases as $identifier => $alias)
        {
            $alias = array_merge($mixer->getIdentifier()->toArray(), $alias);

            if (!$manager->getClass($alias, false)) {
                $manager->registerAlias($identifier, $alias);
            }
        }
    }

    /**
     * Forwards the request to com_files.
     *
     * @param Library\DispatcherContextInterface $context The context object.
     */
    protected function _forward(Library\DispatcherContextInterface $context)
    {
        $mixer = $this->getMixer();

        $parts = $mixer->getIdentifier()->toArray();
        $parts['path'] = ['controller', 'permission'];
        $parts['name'] = 'attachment';

        $permission = $this->getIdentifier($parts)->toString();

        $parts['path'] = ['controller', 'behavior'];

        $behavior = $this->getIdentifier($parts)->toString();

        $parts['path'] = ['controller'];

        $controller = $this->getIdentifier($parts)->toString();

        // Set controller on attachment behavior and push attachment permission to file controller.
        $this->getIdentifier('com:files.controller.file')
             ->getConfig()
             ->append(['behaviors' => [$behavior => ['controller' => $controller], 'permissible' => ['permission' => $permission]]]);

        $context->getRequest()->getQuery()->container = $this->_container;
        $context->param = 'com:files.dispatcher.http';

        $this->forward($context);
    }
}