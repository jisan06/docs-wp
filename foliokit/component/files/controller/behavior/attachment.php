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
 * Attachment Controller Behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ControllerBehaviorAttachment extends Library\ControllerBehaviorAbstract
{
    /**
     * Attachments Controller.
     *
     * @var Library\ControllerInterface|null
     */
    protected $_controller;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_controller = $config->controller;
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['controller' => 'attachment']);

        parent::_initialize($config);
    }

    /**
     * After Add command handler.
     *
     * Creates an attachment entity.
     *
     * @param Library\ControllerContextInterface $context The context object.
     */
    protected function _afterAdd(Library\ControllerContextInterface $context)
    {
        $entity = $context->result;

        if ($entity instanceof ModelEntityNode && $entity->getStatus() !== Library\ModelEntityInterface::STATUS_FAILED)
        {
            $controller = $this->_getController();
            $container  = $entity->getContainer();

            $attachment = $controller->getModel()->name($entity->name)->container($container->id)->fetch();

            if ($attachment->isNew())
            {
                $controller->getRequest()->getQuery()->set('container', $container->id);
                $controller->getModel()->container($container->id);

                $context->attachment = $controller->add(['name' => $entity->name]);
            }
            else $context->attachment = $attachment;
        }
    }

    /**
     * Attachment Controller getter.
     *
     * @return Library\ControllerInterface
     */
    protected function _getController()
    {
        if (!$this->_controller instanceof Library\ControllerInterface)
        {
            $mixer = $this->getMixer();

            if (!$this->_controller instanceof Library\ObjectIdentifierInterface)
            {
                if (strpos($this->_controller, '.') === false)
                {
                    $parts         = $mixer->getIdentifier()->toArray();
                    $parts['name'] = $this->_controller;

                    $identifier = $this->getIdentifier($parts);
                } else $identifier = $this->getIdentifier($this->_controller);
            } else $identifier = $this->_controller;

            $this->_controller = $this->getObject($identifier);
        }

        return $this->_controller;
    }
}