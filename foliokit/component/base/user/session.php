<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Library;

final class UserSession extends Library\UserSessionAbstract implements Library\ObjectSingleton
{
    /**
     * The data array
     *
     * @var array
     */
    protected $_data = [];

    /**
     * The active state of the current session object
     *
     * @var boolean
     */
    protected $_active = false;

    /**
     * Shutdown handler registered
     *
     * @var bool
     */
    protected $_shutdown_handler_registered = false;

    public function start($options = [])
    {
        if (!$this->isActive(true))
        {
            //Make sure we have a registered session handler
            if (!$this->getHandler()->isRegistered()) {
                $this->getHandler()->register();
            }

            @session_cache_limiter('');

            if (ini_get('session.use_cookies') && headers_sent()) {
                throw new \RuntimeException('Failed to start the session because headers have already been sent');
            }

            if (!session_start(['read_and_close' => true])) {
                throw new \RuntimeException('Session could not be started');
            }

            $this->_syncData();

            // Destroy an expired session
            if ($this->getContainer('metadata')->isExpired()) {
                $this->destroy();
            }
        }
        else
        {
            $this->_syncData();

            // Somebody else started the session. Clear the expired session data and stop
            if ($this->getContainer('metadata')->isExpired()) {
                $this->clear();
            }
        }

        // Ensure we can re-start the session on shutdown after the headers are sent
        if (!$this->isActive(true))
        {
            @ini_set('session.use_only_cookies', false);
            @ini_set('session.use_cookies', false);
            @ini_set('session.use_trans_sid', false);
            @ini_set('session.cache_limiter', null);
        }

        $this->_active = true;

        return $this;
    }

    protected function _syncData()
    {
        $namespace = $this->getNamespace();

        //Create the namespace if it doesn't exist
        if(!isset($_SESSION[$namespace])) {
            $_SESSION[$namespace] = array();
        }

        // Copy session data into local cache
        $this->_data = $_SESSION[$namespace];

        if (!$this->_shutdown_handler_registered)
        {
            register_shutdown_function([$this, 'shutdown']);

            $this->_shutdown_handler_registered = true;
        }

        //Re-load the session containers
        $this->refresh();
    }

    /**
     * Refresh the session data in the memory containers
     *
     * This function will load the data from $_SESSION in the various registered containers, based on the container
     * namespace.
     *
     * @return $this
     */
    public function refresh()
    {
        //Re-load the session containers
        foreach($this->_containers as $container) {
            $container->load($this->_data);
        }

        return $this;
    }

    /**
     * Shutdown handler
     */
    public function shutdown()
    {
        if (!ini_get('session.use_cookies') || !headers_sent())
        {
            if (!$this->isActive(true)) {
                session_start();
            }
    
            $namespace = $this->getNamespace();
            $_SESSION[$namespace] = $this->_data;
    
            session_write_close();
        }
    }

    /**
     * Is this session active
     * 
     * @param boolean Tells if the check should be done against PHP or our session object
     *
     * @return boolean  True on success, false otherwise
     */
    public function isActive($php = false)
    {
        if ($php) {
            $result = session_status() === PHP_SESSION_ACTIVE;
        } else {
            $result = $this->_active;
        }

        return $result;
    }

    /**
     * Clear all session data in memory.
     *
     * @see session_unset()
     * @return $this
     */
    public function clear()
    {
        $this->_data = [];

        $namespace = $this->getNamespace();

        //Clear out the session data
        unset($_SESSION[$namespace]);

        //Re-load the session containers
        $this->refresh();

        return $this;
    }

    public function destroy()
    {
        if (!headers_sent())
        {
            if (!$this->isActive(true)) {
                session_start();
            }
        }

        parent::destroy();

        $this->_active = false;

        return $this;
    }

    public function getContainer($name)
    {
        if (!($name instanceof Library\ObjectIdentifier))
        {
            //Create the complete identifier if a partial identifier was passed
            if (is_string($name) && strpos($name, '.') === false)
            {
                $identifier = $this->getIdentifier()->toArray();
                $identifier['path'] = array('session', 'container');
                $identifier['name'] = $name;

                if (!isset($identifier['package']) || $identifier['package'] !== 'user') {
                    array_unshift($identifier['path'], 'user');
                }

                $identifier = $this->getIdentifier($identifier);
            }
            else $identifier = $this->getIdentifier($name);
        }
        else $identifier = $name;

        if (!isset($this->_containers[$identifier->name]))
        {
            $container = $this->getObject($identifier);

            if (!($container instanceof Library\UserSessionContainerInterface))
            {
                throw new \UnexpectedValueException(
                    'Container: '. get_class($container) .' does not implement UserSessionContainerInterface'
                );
            }

            //Load the container from the session
            $container->load($this->_data);

            $this->_containers[$container->getIdentifier()->name] = $container;
        }
        else $container = $this->_containers[$identifier->name];

        return $container;
    }
}