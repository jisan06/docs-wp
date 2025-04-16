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
 * Thumbnailable Controller Behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ControllerBehaviorThumbnailable extends Library\ControllerBehaviorAbstract
{
    protected $_container;

    /**
     * Cannot use isSupported as model is not set on mixer at that time.
     */
    public function execute(Library\CommandInterface $command, Library\CommandChainInterface $chain)
    {
        $result = true;

        if ($this->_canHandle()) {
            $result = parent::execute($command, $chain);
        }

        return $result;
    }

    protected function _canHandle()
    {
        return !!$this->_getContainer();
    }

    protected function _getContainer()
    {
        if (!isset($this->_container))
        {
            $container = false;

            $model = $this->getModel();

            // $model->isThumbnailable() would be more elegant but it's not supported at the moment.
            if ($model->getContainer()->getParameters()->thumbnails_container) {
                $container = $model->getThumbnailsContainer();
            }

            $this->_container = $container;
        }

        return $this->_container;
    }

    protected function _beforeMove(Library\ControllerContextInterface $context)
    {
        $entities = $this->getModel()->fetch();

        $source_folders = [];

        foreach ($entities as $entity) {
            $source_folders[$entity->name] = $entity->folder;
        }

        if (!empty($source_folders)) {
            $context->source_folders = $source_folders;
        }
    }

    protected function _afterMove(Library\ControllerContextInterface $context)
    {
        $entities = $context->result;

        if ($source_folders = $context->source_folders)
        {
            foreach ($entities as $entity)
            {
                $file = $this->_getFile($entity);

                if ($source_folders[$file->name])
                {
                    $file->folder = $source_folders[$file->name];

                    $thumbnails = $this->getObject('com:files.model.thumbnails')
                                       ->source($file->uri)
                                       ->container($this->_getContainer()->slug)->fetch();

                    foreach ($thumbnails as $thumbnail)
                    {
                        $thumbnail->destination_folder = $entity->destination_folder;
                        $thumbnail->destination_name   = $thumbnail->name;

                        $thumbnails->{$context->getAction()}();
                    }
                }
            }
        }
    }

    protected function _beforeCopy(Library\ControllerContextInterface $context)
    {
        $this->_beforeMove($context);
    }

    protected function _afterCopy(Library\ControllerContextInterface $context)
    {
        $this->_afterMove($context);
    }

    protected function _getFile(Library\ModelEntityInterface $entity)
    {
        return $entity;
    }

    protected function _afterAdd(Library\ControllerContextInterface $context)
    {
        $entity = $context->result;

        if ($entity instanceof ModelEntityFile)
        {
            $container = $this->_getContainer();

            // Make sure to cleanup previous thumbnails for new files (specially if overridding)
            $thumbnails = $this->getObject('com:files.model.thumbnails', ['auto_generate' => false])
                               ->container($container->slug)->source($entity->uri)->fetch();

            if (!$thumbnails->isNew()) {
                $thumbnails->delete();
            }
        }
    }

    /*
     * Makes sure that thumbnails are pushed (if needed) after creating a new entity.
     */
    protected function _beforeRender(Library\ControllerContextInterface $context)
    {
        $state  = $this->getModel()->getState();
        $entity = $context->result;

        if ($entity instanceof ModelEntityFile && $entity->isThumbnailable() && $state->thumbnails)
        {
            $parameters = $this->_getContainer()->getParameters();

            if ($versions = $parameters->versions)
            {
                $versions = array_keys($versions->toArray());

                $thumbnail = false;

                if ($state->thumbnails === true) {
                    $thumbnail = $entity->getThumbnail();
                } elseif (in_array($state->thumbnails, $versions)) {
                    $thumbnail = $entity->getThumbnail($state->thumbnails);
                }

                $entity->thumbnail = $thumbnail;
            }
        }
    }

    protected function _afterDelete(Library\ControllerContextInterface $context)
    {
        $entities = $context->result;

        foreach ($entities as $entity)
        {
            $file = $this->_getFile($entity);

            $controller = $this->getObject('com:files.controller.thumbnail')
                               ->container($this->_getContainer()->slug)
                               ->source($file->uri);

            $parameters = $this->_getContainer()->getParameters();

            if ($versions = $parameters->versions) {
                $controller->version(array_keys($versions->toArray()));
            }

            $thumbnails = $controller->browse();

            if ($thumbnails->count()) {
                $thumbnails->delete();
            }
        }
    }
}