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
 * Thumbnailable Database Behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class DatabaseBehaviorThumbnailable extends Library\DatabaseBehaviorAbstract
{
    /**
     * @var array A list of files extensions for which thumbnails may be generated
     */
    protected $_thumbnailable_extensions;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_thumbnailable_extensions = Library\ObjectConfig::unbox($config->thumbnailable_extensions);
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['thumbnailable_extensions' => ModelEntityFile::$image_extensions]);

        parent::_initialize($config);
    }

    /**
     * Tells if a thumbnail should be generated for the file
     *
     * @param array $dimension An array specifying the dimension of the thumbnail in pixels
     *
     * @return bool true if it can, false otherwise
     */
    public function canHaveThumbnail($dimension = null)
    {
        $result = false;
        $mixer  = $this->getMixer();

        if ($mixer instanceof ModelEntityFile && !$mixer->isNew()) {
            $result = in_array($mixer->extension, $this->_thumbnailable_extensions);
        }

        if ($result && is_array($dimension))
        {
            // Check source size against thumbnail size (local sources only)
            if ($mixer->isLocal() && ($size = $mixer->adapter->getImageSize()))
            {
                if (isset($dimension['width']) && isset($dimension['height']))
                {
                    if ($size['width'] <= $dimension['width'] && $size['height'] <= $dimension['height']) $result = false;
                }
                elseif (isset($dimension['width']))
                {
                    if ($size['width'] <= $dimension['width']) $result = false;
                }
                elseif (isset($dimension['height']))
                {
                    if ($size['height'] <= $dimension['height']) $result = false;
                }
            }
        }

        return $result;
    }

    /**
     * Thumbnail getter
     *
     * @param string $version The version model state value
     *
     * @return ModelEntityThumbnails|false The thumbnails entity object, false if an empty entity is returned or
     *                                             if the model could not be fetched
     */
    public function getThumbnail($version =  null)
    {
        $thumbnail = false;

        if ($container = $this->thumbnails_container_slug)
        {
            $model = $this->getObject('com:files.model.thumbnails')->container($container)->source($this->uri);

            if ($version) {
                $model->version($version);
            }

            $thumbnail = $model->fetch();

            if ($thumbnail->isNew()) {
                $thumbnail = false;
            }
        }

        return $thumbnail;
    }
}