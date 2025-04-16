<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/nooku/nooku-framework-wordpress for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Library;

/**
 * Abstract Router Route
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Dispatcher\Router\Route
 */
abstract class DispatcherRouterRouteAbstract extends Library\HttpUrl implements DispatcherRouterRouteInterface
{
    /**
     * The status
     *
     * Available entity status values are defined as STATUS_ constants
     *
     * @var integer
     */
    protected $_status = null;

    /**
     * The initial route
     *
     * @var DispatcherRouterRouteInterface
     */
    protected $_initial_route = null;

    /**
     * Constructor
     *
     * @param Library\ObjectConfig $config  An optional Library\ObjectConfig object with configuration options
     */
    public function __construct(Library\ObjectConfig $config)
    {
		parent::__construct($config);

        //Set the url
        $this->setQuery(Library\ObjectConfig::unbox($config->query), true);

		if ($port = $this->getPort())
		{
			$path = $port;

			if ($this->getPath()) {
				$path = sprintf('%s/%s', $path, ltrim($this->getPath(), '/'));
			}

			$this->setPath((string) $path);

			unset($this->port);
		}

        //Store the initial state
        $this->_initial_route = clone $this;
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation
     *
     * @param   Library\ObjectConfig $config  An optional Library\ObjectConfig object with configuration options
     * @return  void
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(array(
            'query'  => array(),
        ));

        parent::_initialize($config);
    }

    /**
     * Get the route state
     *
     * @return array
     */
    public function getState()
    {
        return array();
    }

    /**
     * Get the format
     *
     * @return string
     */
    public function getFormat()
    {
        return pathinfo($this->getPath(), PATHINFO_EXTENSION);
    }

    /**
     * Mark the route as resolved
     *
     * @return DispatcherRouterRouteInterface
     */
    public function setResolved()
    {
        $this->_status = self::STATUS_RESOLVED;
        return $this;
    }

    /**
     * Mark the route as generated
     *
     * @return DispatcherRouterRouteInterface
     */
    public function setGenerated()
    {
        $this->_status = self::STATUS_GENERATED;
        return $this;
    }

    /**
     * Test if the route has been resolved
     *
     * @return	bool
     */
    public function isResolved()
    {
        return (bool) ($this->_status == self::STATUS_RESOLVED);
    }

    /**
     * Test if the route has been generated
     *
     * @return	bool
     */
    public function isGenerated()
    {
        return (bool) ($this->_status == self::STATUS_GENERATED);
    }

    /**
     * Test if the route is absolute
     *
     * @return	bool
     */
    public function isAbsolute()
    {
        return (bool) ($this->scheme && $this->host);
    }
}
