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
 * Mimetype Mixin
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class MixinMimetype extends Library\ObjectAbstract
{
	/**
	 * Used as a way to continue on the chain when the method is not available.
	 */
	const NOT_AVAILABLE = -1;

	/**
	 * Adapters to use for mimetype detection
	 *
	 * @var array
	 */
	protected $_adapters = [];

/**
     * Static mimetype cache indexed by file extension
     *
     * @var array
     */
    protected static $_mimetype_cache = array();

	public function __construct(Library\ObjectConfig $config)
	{
		parent::__construct($config);

		if (isset($config->adapters)) {
			$this->_adapters = Library\ObjectConfig::unbox($config->adapters);
		}
	}

	protected function _initialize(Library\ObjectConfig $config)
	{
		if (empty($config->adapters)) {
			$config->adapters = ['image', 'extension'];
		}

		parent::_initialize($config);
	}

	public function getMimetype($path)
	{
		$mimetype = false;

		if (!file_exists($path)) {
			return $mimetype;
		}

		foreach ($this->_adapters as $i => $adapter)
		{
			$function = '_detect'.ucfirst($adapter);
			$return = $this->$function($path);

			if (!empty($return) && $return !== MixinMimetype::NOT_AVAILABLE) {
				$mimetype = $return;
				break;
			}
		}

		// strip charset from text files
		if (!empty($mimetype) && strpos($mimetype, ';')) {
			$mimetype = substr($mimetype, 0, strpos($mimetype, ';'));
		}

		// special case: empty text files
		if ($mimetype == 'application/x-empty' || $mimetype === 'inode/x-empty') {
			$mimetype = 'text/plain';
		}

        // special case: Microsoft BMP mimetype
        if ($mimetype == 'image/x-ms-bmp') {
            $mimetype = 'image/bmp';
        }
		
		return $mimetype;
	}

	protected function _detectImage($path)
	{
		if (in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ModelEntityFile::$image_extensions)
			&& ($info = @getimagesize($path))
        ) {
			return $info['mime'];
		}

		return MixinMimetype::NOT_AVAILABLE;
	}

	protected function _detectExtension($path)
    {
        $mimetype = MixinMimetype::NOT_AVAILABLE;

        if ($extension = strtolower(pathinfo($path, PATHINFO_EXTENSION)))
        {
			// Check static cache first
            if (isset(static::$_mimetype_cache[$extension])) {
                return static::$_mimetype_cache[$extension];
            }
            
            $entity = $this->getObject('com:files.model.mimetypes')->extension($extension)->fetch();

            if (!$entity->isNew()) {
                $mimetype = $entity->mimetype;

                // Store in static cache
                static::$_mimetype_cache[$extension] = $mimetype;
            }
        }

        return $mimetype;
    }

	protected function _detectFinfo($path)
	{
		if (!class_exists('finfo')) {
			return MixinMimetype::NOT_AVAILABLE;
		}

		$finfo = @new \finfo(FILEINFO_MIME);
		
		if (empty($finfo)) {
		    return MixinMimetype::NOT_AVAILABLE;
		}
		
		$mimetype = $finfo->file($path);

		return $mimetype;
	}

	/**
	 * Not used by default since it can't use our magic.mime file and cannot be reliable.
	 * It's also deprecated by PHP in favor of fileinfo extension used above.
	 */
	protected function _detectMime_content_type($path)
	{
		if (!function_exists('mime_content_type')) {
			return MixinMimetype::NOT_AVAILABLE;
		}

		return mime_content_type($path);
	}
}
