<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

/**
 * User Provider Singleton
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\User\Provider
 */
class UserProvider extends Library\ObjectDecorator implements Library\UserProviderInterface
{
    /**
     * Static cache of user objects
     *
     * @var array
     */
    protected static $_cache = array();
    
    public function getUser($identifier)
    {
        // Check static cache first
        if ($this->hasCache($identifier)) {
            return $this->getCache($identifier);
        }
        
        $user = $this->getDelegate()->getUser($identifier);

        if (!$user instanceof User) {
            $user = $user->decorate('com:easydoc.user');
        }
        
        // Store in cache
        $this->setCache($identifier, $user);

        return $user;
    }

    public function setUser(Library\UserInterface $user)
    {
        return $this->getDelegate()->setUser($user);
    }

    public function findUser($identifier)
    {
        // Check static cache first
        if ($this->hasCache($identifier)) {
            return $this->getCache($identifier);
        }
        
        $user = $this->getDelegate()->findUser($identifier);

        if (!is_null($user ) && !$user instanceof User) {
            $user = $user->decorate('com:easydoc.user');
        }
        
        // Store in cache if user was found
        if (!is_null($user)) {
            $this->setCache($identifier, $user);
        }

        return $user;
    }

    public function fetch($identifier, $lazyload= false)
    {
        return $this->getDelegate()->fetch($identifier, $lazyload);
    }

    public function create($data)
    {
        return $this->getDelegate()->create($data);
    }

    public function isLoaded($identifier)
    {
        return $this->getDelegate()->isLoaded($identifier);
    }

    /**
     * Store a user in the static cache
     *
     * @param mixed $identifier The user identifier
     * @param Library\UserInterface $user The user object
     * @return void
     */
    protected function setCache($identifier, Library\UserInterface $user)
    {
        // Use a string key for the cache
        $key = is_scalar($identifier) ? (string)$identifier : md5(serialize($identifier));
        static::$_cache[$key] = $user;
    }
    
    /**
     * Check if a user exists in the static cache
     *
     * @param mixed $identifier The user identifier
     * @return bool True if the user is cached, false otherwise
     */
    protected function hasCache($identifier)
    {
        $key = is_scalar($identifier) ? (string)$identifier : md5(serialize($identifier));
        return isset(static::$_cache[$key]);
    }
    
    /**
     * Get a user from the static cache
     *
     * @param mixed $identifier The user identifier
     * @return Library\UserInterface|null The cached user or null if not found
     */
    protected function getCache($identifier)
    {
        $key = is_scalar($identifier) ? (string)$identifier : md5(serialize($identifier));
        return $this->hasCache($identifier) ? static::$_cache[$key] : null;
    }
}