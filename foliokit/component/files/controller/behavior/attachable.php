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
 * Attachable Controller Behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ControllerBehaviorAttachable extends Library\ControllerBehaviorAbstract
{
    /**
     * The attachment controller.
     *
     * @var Library\ControllerInterface|null
     */
    protected $_controller;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_controller  = $config->controller;
    }

    /**
     * Before Attach command handler.
     *
     * Serves as a validation check.
     *
     * @param Library\ControllerContextInterface $context The context object.
     */
    protected function _beforeAttach(Library\ControllerContextInterface $context)
    {
        $entity = $context->getSubject()->getModel()->fetch();

        if ($entity->isNew()) {
            throw new \RuntimeException('Entity does not exists');
        }

        $context->entity = $entity;
    }

    /**
     * Attach action.
     *
     * Forwards the action to the attachment controller with formatted data.
     *
     * @param Library\ControllerContextInterface $context The context object.
     */
    protected function _actionAttach(Library\ControllerContextInterface $context)
    {
        $this->_getController()->attach($this->_getData($context));
    }

    protected function _afterAttach(Library\ControllerContextInterface $context)
    {
        $context->getResponse()->setStatus(Library\HttpResponse::NO_CONTENT);
    }

    protected function _beforeDetach(Library\ControllerContextInterface $context)
    {
        $this->_beforeAttach($context);
    }

    /**
     * Detach action.
     *
     * Forwards the action to the attachment controller with formatted data.
     *
     * @param Library\ControllerContextInterface $context The context object.
     */
    protected function _actionDetach(Library\ControllerContextInterface $context)
    {
        $this->_getController()->detach($this->_getData($context));
    }

    protected function _afterDetach(Library\ControllerContextInterface $context)
    {
        $this->_afterAttach($context);
    }

    /**
     * Attachment controller getter.
     *
     * @return Library\ControllerInterface
     */
    protected function _getController()
    {
        if (!$this->_controller instanceof Library\ControllerInterface)
        {
            $mixer = $this->getMixer();

            $parts         = $mixer->getIdentifier()->toArray();
            $parts['name'] = 'attachment';

            $identifier = $this->getIdentifier($parts);

            $query = $mixer->getRequest()->getQuery();
            $data  = $mixer->getRequest()->getData();

            $request = $this->getObject('lib:controller.request', [
                'query' => [
                    'name'      => $data->attachment,
                    'container' => $query->container
                ]
            ]);

            $this->_controller = $this->getObject($identifier, ['request' => $request]);
        }

        return $this->_controller;
    }

    /**
     * POST data getter.
     *
     * @param Library\ControllerContextInterface $context The context object.
     * @return array The data
     */
    protected function _getData(Library\ControllerContextInterface $context)
    {
        $entity = $context->entity;

        return ['table' => $entity->getTable()->getBase(), 'row' => $entity->id];
    }
}