<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

#[\AllowDynamicProperties]
class ControllerThumbnail extends Library\ControllerAbstract implements Library\ObjectMultiton
{
    protected static $_extensions = ['jpg', 'jpeg', 'gif', 'png', 'bmp'];

    /**
     * @var mixed The thumbnail container.
     */
    protected $_container;

    /**
     * @var string The folder (relative to container's root) where generated thumbnails will be stored.
     */
    protected $_folder;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->setThumbnailSize(Library\ObjectConfig::unbox($config->thumbnail_size));

        $this->_container = $config->container;
        $this->_folder    = $config->folder;
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'supported_extensions' => static::$_extensions,
            'container'            => 'easydoc-images',
            'folder'               => 'generated',
            'thumbnail_size'       => ['x' => 512, 'y' => 512]
        ]);

        parent::_initialize($config);
    }

    protected function _actionGenerate(Library\ControllerContextInterface $context)
    {
        $result    = false;
        $entity    = $context->getAttribute('entity');
        $thumbnail = $context->getAttribute('thumbnail');

        if (!$thumbnail)
        {
            if ($entity->storage_type == 'file' && in_array($entity->extension, static::$_extensions))
            {
                $thumbnail = $this->_createThumbnail($entity->storage->fullpath);

                $context->setAttribute('thumbnail', $thumbnail);
            }
        }

        if ($thumbnail) {
            $result = $this->execute('save', $context);
        } else {
            $entity->image = '';
            $result = (bool) $entity->save();
        }

        return $result;
    }

    protected function _actionSave(Library\ControllerContextInterface $context)
    {
        $result    = false;
        $container = $this->getContainer();
        $entity    = $context->getAttribute('entity');
        $thumbnail = $context->getAttribute('thumbnail');

        try
        {
            $data = [
                'file'      => $thumbnail,
                'name'      => $this->getDefaultFilename($entity, false),
                'folder'    => $this->_folder,
                'overwrite' => true
            ];

            $image = $this->getObject('com:files.controller.file', [
                'behaviors' => [
                    'permissible' => [
                        'permission' => 'com://admin/easydoc.controller.permission.file'
                    ]
                ]])->container($container->slug)->add($data);

            $entity->image = $image->path;
            $result = (bool) $entity->save();

        }
        catch (Library\ControllerException $e) {}

        return $result;
    }

    public function getSupportedExtensions()
    {
        return $this->getConfig()->supported_extensions->toArray();
    }

    public function getDefaultFilename($entity, $path = true)
    {
        $filename  = md5($entity->id ?: '');
        $extension = 'jpg';

        $container_path = $this->getContainer()->fullpath;
        if(file_exists($container_path . '/' . $this->_folder  . '/' . $filename . '.png' )) {
            $extension = 'png';
        }

        $filename = $filename.'.'.$extension;

        if ($path) {
            $filename = $this->_folder . '/' . $filename;
        }

        return $filename;
    }

    public function getThumbnailSize()
    {
        return $this->_thumbnail_size;
    }

    /**
     * @param array $size An array with x and y properties
     * @return $this
     */
    public function setThumbnailSize(array $size)
    {
        $this->_thumbnail_size = $size;
        return $this;
    }

    protected function _createThumbnail($file)
    {
        @ini_set('memory_limit', '256M');
        @ini_set('memory_limit', '512M');

        $result = false;

        try
        {
            if ($this->_canGenerate($file))
            {
                $imagine = new \Imagine\Gd\Imagine();
                $image   = $imagine->open($file);

                $size = $this->getThumbnailSize();

                $image_size = $image->getSize();
                $larger     = max($image_size->getWidth(), $image_size->getHeight());
                $scale      = max($size['x'], $size['y']);
                $new_size   = $image_size->scale(1/($larger/$scale));

                $image = $image->thumbnail($new_size, \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND);

                $file = new \SplTempFileObject();
                $file->fwrite($image->get('jpg'));

                $result = $file;
            } else {
                $message = $this->getObject('translator')->translate('Cannot generate thumbnail locally', [
                    'link' => 'https://ait-themes.club/',
                    'product' => 'EasyDocs'
                ]);

                $this->getObject('response')->addMessage($message, Library\ControllerResponse::FLASH_SUCCESS);
            }
        }
        catch (Exception $e) {}

        return $result;
    }

    /**
     * Checks if a thumbnail for the current source and provided size can be generated given the
     * amount of memory that's available.
     *
     * @param  string $file Source file
     * @return bool True if the thumbnail can be "safely" processed, false otherwise.
     */
    protected function _canGenerate($file)
    {
        $result = false;

        // Multiplier to take into account memory consumed by the Image Processing Library.
        $tweak_factor  = 6;

        $source = @getimagesize($file);

        $channels      = isset($source['channels']) ? $source['channels'] : 4;
        $bits          = isset($source['bits']) ? $source['bits'] : 8;
        $source_memory = ceil($source[0] * $source[1] * $bits * $channels / 8 * $tweak_factor);

        $thumb = $this->getThumbnailSize();

        // We assume the same amount of bits and channels as source.
        $thumb_memory = ceil($thumb['x'] * $thumb['y'] * $bits * $channels / 8 * $tweak_factor);

        //If memory is limited
        $limit = ini_get('memory_limit');
        if ($limit != '-1')
        {
            $limit = ControllerBehaviorThumbnailable::convertToBytes($limit);
            $available_memory = $limit - memory_get_usage();

            // Leave 16 megs for the rest of the request
            $available_memory -= 16777216;

            if ($source_memory + $thumb_memory < $available_memory) {
                $result = true;
            }
        }
        else $result = true;

        return $result;
    }

    public function getContainer()
    {
        if (!$this->_container instanceof Library\ModelEntityInterface)
        {
            $container = $this->getObject('com:files.model.containers')
                ->slug($this->_container)
                ->fetch();

            $this->setContainer($container);
        }

        return $this->_container;
    }

    public function setContainer(Library\ModelEntityInterface $container)
    {
        $this->_container = $container;
        $folder           = $container->fullpath . '/' . $this->_folder;

        if (!file_exists($folder)) {
            \EasyDocLabs\WP::wp_mkdir_p($folder);
        }

        $container->folder = $folder . '/';

        return $this;
    }
}