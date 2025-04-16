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
use EasyDocLabs\WP;

/**
 * User
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
final class User extends Library\User implements UserInterface
{
    /**
     * Returns the id of the user
     *
     * @return int The id
     */
    public function getId()
    {
        return WP::get_current_user_id();
    }

    /**
     * Returns the email of the user
     *
     * @return string The email
     */
    public function getEmail()
    {
        return WP::wp_get_current_user()->user_email;
    }

    /**
     * Returns the name of the user
     *
     * @return string The name
     */
    public function getName()
    {
        return WP::wp_get_current_user()->display_name;
    }

    /**
     * Returns the user language tag
     *
     * Should return a properly formatted IETF language tag, eg xx-XX
     * @link https://en.wikipedia.org/wiki/IETF_language_tag
     * @link https://tools.ietf.org/html/rfc5646
     *
     * @return string
     */
    public function getLanguage()
    {
        return get_user_locale($this->getId());
    }

    /**
     * Returns the user timezone
     *
     * @return string
     */
    public function getTimezone()
    {
        return null;
    }

    /**
     * Returns the roles of the user
     *
     * @return array An array of role identifiers
     */
    public function getRoles()
    {
        return WP::wp_get_current_user()->get_role_caps();
    }

    /**
     * Returns the groups the user is part of
     *
     * @return array An array of group identifiers
     */
    public function getGroups()
    {
        return [];
    }

    /**
     * Returns the username of the user
     *
     * @return string The name
     */
    public function getUsername()
    {
        return WP::wp_get_current_user()->user_login;
    }

    /**
     * Checks whether the user is not logged in
     *
     * @param  boolean $strict If true, checks if the user has been authenticated for this request explicitly
     * @return boolean True if the user is not logged in, false otherwise
     */
    public function isAuthentic($strict = false)
    {
        $result = (bool) WP::get_current_user_id();

        if ($strict) {
            $result = $result && $this->_authentic;
        }

        return $result;
    }

    /**
     * Checks whether the user is enabled.
     *
     * @return Boolean true if the user is not logged in, false otherwise
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * Checks whether the user account has expired.
     *
     * @return Boolean
     */
    public function isExpired()
    {
        return false;
    }

    /**
     * Sets the user as authenticated for the request
     *
     * @return $this
     */
    public function setAuthentic()
    {
        $this->_authentic = true;

        return $this;
    }

    /**
     * Get the user data as an array
     *
     * @return array An associative array of data
     */
    public function toArray()
    {
        return [
            'id'         => $this->getId(),
            'email'      => $this->getEmail(),
            'name'       => $this->getName(),
            'username'   => $this->getUsername(),
            'authentic'  => $this->isAuthentic(),
            'parameters' => $this->getId() ? WP::get_user_meta($this->getId()) : []
        ];
    }

    /**
     * Set the user properties from an array
     *
     * @param  array $properties An associative array
     * @return User
     */
    public function setProperties($properties)
    {
        Library\UserAbstract::setProperties($properties);

        return $this;
    }

    /**
     * Get an user parameter
     *
     * @param string $name The parameter name
     * @param   mixed   $default      Default value when the attribute doesn't exist
     * @return  mixed   The value
     */
    public function get($name, $default = null)
    {
        $value = WP::get_user_meta($this->getId(), $name, true);

        return !empty($value) ? $value : $default;
    }

    /**
     * Set an user parameter
     *
     * @param string $name The parameter name
     * @param  mixed $value The parameter value
     * @return User
     */
    public function set($name, $value)
    {
        WP::update_user_meta($this->getId(), $name, $value);

        return $this;
    }

    /**
     * Check if a user parameter exists
     *
     * @param string $name The parameter name
     * @return  boolean
     */
    public function has($name)
    {
        return (bool) $this->get($name);
    }

    /**
     * Removes an user parameter
     *
     * @param string $name The parameter name
     * @return User
     */
    public function remove($name)
    {
        WP::delete_user_meta($this->getId(), $name);

        return $this;
    }

    /**
     * Method to check object authorisation
     *
     * @param   string  $action  The name of the action to check for permission.
     * @param   string  $object  The name of the object on which to perform the action.
     * @return  boolean  True if authorised
     */
    public function authorise($action, $object = null)
    {
        return WP::wp_get_current_user()->has_cap($action);
    }
}