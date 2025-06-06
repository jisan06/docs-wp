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
 * Thumbnails Model
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ModelThumbnails extends ModelFiles
{
    protected $_source_file;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        if ($config->auto_generate) {
            $this->addCommandCallback('after.fetch', '_checkThumbnails');
        }

    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['state' => 'com:files.model.state.thumbnails', 'auto_generate' => true]);
        parent::_initialize($config);
    }

    protected function _actionCreate(Library\ModelContext $context)
    {
        $parameters = $this->getContainer()->getParameters();
        $state      = $this->getState();
        $entity     = $context->properties;

        $entity->name   = $state->name;
        $entity->folder = $state->folder;

        if ($file = $this->_getSourceFile()) {
            $entity->source = $file;
        }

        if ($versions = $parameters->versions)
        {
            if ($version = $state->version)
            {
                if ($config = $versions->{$version})
                {
                    $entity->version   = $version;
                    $entity->name      = $version . '-' . $entity->name;
                    $entity->dimension = $config->dimension->toArray();
                    $entity->crop      = $config->crop;
                }
            }
        }
        else
        {
            if ($dimension = $parameters->dimension) {
                $entity->dimension = $dimension;
            }

            if (isset($parameters->crop)) {
                $entity->crop = $parameters->crop;
            }
        }

        return parent::_actionCreate($context);
    }

    /**
     * Reset the cached container object if container changes
     *
     * @param Library\ModelContext $context
     */
    protected function _afterReset(Library\ModelContext $context)
    {
        parent::_afterReset($context);

        $modified = (array) Library\ObjectConfig::unbox($context->modified);

        if (in_array('source', $modified)) {
            $this->_source_file = null;
        }
    }

    protected function _getSourceFile()
    {
        if (!$this->_source_file instanceof ModelEntityFile)
        {
            $state = $this->getState();

            if ($state->source) {
                $this->_source_file = $state->getSourceFile();
            }
        }

        return $this->_source_file;
    }

    protected function _beforeCreateSet(Library\ModelContext $context)
    {
        $parameters = $this->getContainer()->getParameters();

        if ($thumbnails = $context->files)
        {
            $file = $this->_getSourceFile();

            foreach ($thumbnails as $thumbnail)
            {
                if ($file) {
                    $thumbnail->source = $file;
                }

                if ($versions = $parameters->versions)
                {
                    $versions = array_keys($versions->toArray());

                    foreach ($versions as $version)
                    {
                        if (strpos($thumbnail->name, $version) === 0) {
                            break;
                        }
                    }

                    $config = $parameters->versions->{$version};

                    $thumbnail->dimension = $config->dimension;
                    $thumbnail->crop      = $config->crop;
                    $thumbnail->version   = $version;
                }
                else
                {
                    if ($dimension = $parameters->dimension) {
                        $thumbnail->dimension = $dimension;
                    }

                    if (isset($parameters->crop)) {
                        $thumbnail->crop = $parameters->crop;
                    }
                }
            }
        }
    }

    public function iteratorFilter($path)
    {
        $state     = $this->getState();
        $filename  = \Foliokit\basename($path);

        if ($filename && $filename[0] === '.') {
            return false;
        }

        if ($name = $state->name)
        {
            $names = [];

            $parameters = $this->getContainer()->getParameters();

            if ($parameters->versions)
            {
                if ($version = $state->version) {
                    $versions = (array) $version;
                } else {
                    $versions = array_keys($parameters->versions->toArray());
                }

                foreach ($versions as $version) {
                    $names[] = $version . '-' . $name;
                }
            }
            else $names[] = $name;

            if (!in_array($filename, $names)) {
                return false;
            }
        }

        if ($state->search && stripos($filename, $state->search) === false) {
            return false;
        }
    }

    protected function _beforeFetch(Library\ModelContext $context)
    {
        $state = $this->getState();

        if ($folder = $state->folder)
        {
            $container = $this->getContainer();


            $folder = $this->getObject('com:files.adapter.folder', [
                'path' => $container->fullpath . '/' . $folder
            ]);

            // Avoid 'Invalid Folder' error on thumbs model (create folder if it doesn't exists)
            if (!$folder->exists()) {
                $folder->create();
            }
        }

        parent::_beforeFetch($context);
    }

    protected function _generateThumbnails(Library\ModelContext $context)
    {
        if ($context->entity && $context->entity instanceof ModelEntityThumbnails) {
            $thumbnails = $context->entity;
        } else {
            $thumbnails = $this->getObject('com:files.model.entity.thumbnails');
        }

        $file = $this->_getSourceFile();

        if ($file && $file->canHaveThumbnail())
        {
            $state     = $this->getState();
            $container = $this->getContainer();

            // Name and folder are set again to allow name and folder overrides for the target file
            $model = $this->getObject('com:files.model.thumbnails')
                          ->container($container->slug)
                          ->source($state->source)
                          ->name($state->name)
                          ->folder($state->folder);

            if ($versions = $container->getParameters()->versions)
            {
                $versions = array_keys($versions->toArray());

                if ($version = $state->version) {
                    $versions = array_intersect($versions, (array) $version);
                }

                foreach ($versions as $version)
                {
                    $thumbnail = $model->version($version)->create();

                    if (!$thumbnails->offsetExists($thumbnail) && $file->canHaveThumbnail($thumbnail->dimension))
                    {
                        if ($thumbnail->save()) {
                            $thumbnails->insert($thumbnail);
                        }
                    }
                }
            }
            else
            {
                $thumbnail = $model->create();

                if ($file->canHaveThumbnail($thumbnail->dimension) && $thumbnail->save()) {
                    $thumbnails->insert($thumbnail);
                }
            }
        }

        return $thumbnails;
    }

    protected function _checkThumbnails(Library\ModelContext $context)
    {
        $file = $this->_getSourceFile();

        if ($file)
        {
            $parameters = $this->getContainer()->getParameters();
            $state      = $context->getState();
            $thumbnails = $context->entity;

            if (($versions = $parameters->versions) && !$state->version)
            {
                if ($thumbnails->count() !== count($versions)) {
                    $this->_generateThumbnails($context); // Generate missing thumbnails
                }
            }
            elseif ($thumbnails->isNew()) $this->_generateThumbnails($context);
        }
    }
}
