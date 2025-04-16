<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Files;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

/**
 * Attachment Controller
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ControllerAttachment extends Base\ControllerModel
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        if ($this->getIdentifier()->package != 'files')
        {
            $aliases = [
                'com:files.model.attachments'                => [
                    'path' => ['model'],
                    'name' => 'attachments'
                ],
                'com:files.model.behavior.attachable'        => [
                    'path' => ['model', 'behavior'],
                    'name' => 'attachable'
                ],
                'com:files.controller.permission.attachment' => [
                    'path' => ['controller', 'permission'],
                    'name' => 'attachment'
                ]
            ];

            $manager = $this->getObject('manager');

            foreach ($aliases as $identifier => $alias)
            {
                $alias = array_merge($this->getIdentifier()->toArray(), $alias);

                if (!$manager->getClass($alias, false)) {
                    $manager->registerAlias($identifier, $alias);
                }
            }
        }

        parent::_initialize($config);
    }

    /**
     * Before Render command handler.
     *
     * Pushes permissions to the view.
     *
     * @param Library\ControllerContextInterface $context The context object.
     */
    protected function _beforeRender(Library\ControllerContextInterface $context)
    {
        $view = $this->getView();

        $view->getConfig()->append([
            'config' => [
                'can_attach' => $this->canAttach(),
                'can_detach' => $this->canDetach()
            ]
        ]);
    }

    /**
     * Before Attach command handler.
     *
     * Makes sure that there's an attachment and that this attachment exists.
     *
     * @param Library\ControllerContextInterface $context The context object.
     */
    protected function _beforeAttach(Library\ControllerContextInterface $context)
    {
        $model = $this->getModel();

        $column = $model->getTable()->getIdentityColumn();

        $context->identity_column = $column;

        if (!$context->attachment) {
            $context->attachment = $this->getModel()->fetch();
        }

        if ($context->attachment->isNew())
        {
            $state = $model->getState();

            $container = $this->getObject('com:files.model.containers')->id($state->container)->fetch();

            $file = $this->getObject('com:files.model.files')->container($container->slug)->name($state->name)->fetch();

            // Check if a file in the given container exists.
            if (!$file->isNew())
            {
                // Create the attachment entry.
                $controller = $this->getObject($this->getIdentifier());
                $controller->getRequest()->getQuery()->container = $this->getRequest()->getQuery()->container;
                $context->attachment = $controller->add(['name' => $model->getState()->name]);
            }
            else throw new \RuntimeException('Attachment does not exists');
        }
    }

    /**
     * Attach action.
     *
     * Creates a relationship between a resource and an existing attachment.
     *
     * @param Library\ControllerContextInterface $context The context object.
     */
    protected function _actionAttach(Library\ControllerContextInterface $context)
    {
        $model = $this->getModel()->getRelationsModel();
        $data  = $context->getRequest()->getData();

        $data[$context->identity_column] = $context->attachment->id;

        $relation = $model->create($data->toArray());

        if (!$relation->save()) {
            throw new \RuntimeException('Could not attach');
        }

        $context->relation = $relation;
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
     * Removes a relationship between a resource and an existing attachment.
     *
     * @param Library\ControllerContextInterface $context The context object.
     */
    protected function _actionDetach(Library\ControllerContextInterface $context)
    {
        $model = $this->getModel()->getRelationsModel();

        $relation = $model->{$context->identity_column}($context->attachment->id)
                          ->setState($this->getRequest()->getData()->toArray())->fetch();

        if (!$relation->isNew())
        {
            if (!$relation->delete()) {
                throw new \RuntimeException('Could not detach');
            }
        }
    }

    protected function _afterDetach(Library\ControllerContextInterface $context)
    {
        $model = $this->getModel()->getRelationsModel();

        $model->getState()->reset();

        $attachment = $context->attachment;

        if (!$model->{$context->identity_column}($attachment->id)->count())
        {
            if (!$attachment->delete()) {
                throw new \RuntimeException(('Attachment could not be deleted'));
            }
        }

        $this->_afterAttach($context);
    }

    /**
     * Overriden for auto-aliasing views when the controller is extended.
     */
    public function setView($view)
    {
        $view = parent::setView($view);

        if ($view instanceof Library\ObjectIdentifierInterface && $view->getPackage() !== 'files')
        {
            $manager = $this->getObject('manager');

            if (!$manager->getClass($view, false))
            {
                $identifier = $view->toArray();
                $identifier['package'] = 'files';
                unset($identifier['domain']);

                $manager->registerAlias($identifier, $view);
            }
        }

        return $view;
    }
}