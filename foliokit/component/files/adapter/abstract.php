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
 * Abstract Local Adapter
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
abstract class AdapterAbstract extends Library\ObjectAbstract
{
	/**
	 * Path to the node
	 */
	protected $_path = null;

	/**
	 * A pointer for the FileInfo object
	 */
	protected $_handle = null;

    /**
     * @var bool Tells if the adapter points to a local resource
     */
	protected $_local;

	protected $_metadata;

	public function __construct(Library\ObjectConfig $config)
	{
		parent::__construct($config);

        $this->setPath($config->path);
	}

	protected function _initialize(Library\ObjectConfig $config)
	{
        $config->append(['path' => '']);

		parent::_initialize($config);
	}

	public function isLocal()
    {
        return (bool) $this->_local;
    }

	public function setPath($path)
	{
		$path = $this->normalize($path);

		$this->_path = $path;
		$this->_handle = new \SplFileInfo($path);

		$this->_metadata = null;

        $parts = parse_url($this->_path);

        $this->_local = true;

        if (isset($parts['scheme']))
        {
            $scheme = $parts['scheme'];

            if ($scheme === 'file') {
                $this->_path = str_replace('file://', '', $this->_path);
            } else {
                $this->_local = false;
            }
        }

		return $this;
	}

	public function getName()
	{
        $path = $this->_handle->getBasename();

        return $this->normalize(\Foliokit\basename($path));
	}

	public function getPath()
	{
		return $this->normalize($this->_handle->getPathname());
	}

	public function getDirname()
	{
		return $this->normalize(dirname($this->_handle->getPathname()));
	}

	public function getRealPath()
	{
		return $this->_path;
	}

	public function normalize($string)
	{
		return str_replace('\\', '/', $string);
	}
}
