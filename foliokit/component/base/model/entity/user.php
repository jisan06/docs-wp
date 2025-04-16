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
 * User entity
 *
 * @package EasyDocLabs\Component\Base
 */
class ModelEntityUser extends Library\ModelEntityRow implements UserInterface
{
    /**
     * A whitelist of fields visible in the JSON representation
     *
     * @var array
     */
    protected $_fields = [];

    /**
     * The User Groups
     *
     *  @var Library\ModelEntityInterface
     */
    protected $_groups;

    /**
     * Returns the id of the user
     *
     * @return int The id
     */
    public function getId()
    {
        return $this->id;
    }

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        // Only allow fields in the config option for security reasons
        $this->_fields = array_fill_keys(Library\ObjectConfig::unbox($config->fields), null);
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        if (empty($config->fields)) {
            $config->fields = ['id', 'name'];
        }

        parent::_initialize($config);
    }

    /**
     * Returns the email of the user
     *
     * @return string The email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Returns the name of the user
     *
     * @return string The name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the username of the user
     *
     * @return string The name
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the users language
     *
     * Should return a properly formatted IETF language tag, eg xx-XX
     * @link https://en.wikipedia.org/wiki/IETF_language_tag
     * @link https://tools.ietf.org/html/rfc5646
     *
     * @return string The language tag
     */
    public function getLanguage()
    {
        return $this->get('language');
    }

    /**
     * Returns the users timezone
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->get('timezone');
    }

    /**
     * Returns the roles of the user
     *
     * @return array The role names
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Checks if the user has a role.
     *
     * @param  mixed|array $role A role name or an array containing role names.
     * @param bool $strict If true, the user has to have all the provided roles, not just one
     * @return bool
     */
    public function hasRole($role, $strict = false)
    {
        $roles = (array) $role;

        if($strict) {
            $result = !array_diff($roles, $this->getRoles());
        } else {
            $result =  (bool) array_intersect($this->getRoles(), $roles);
        }

        return $result;
    }

    /**
     * Returns the groups the user is part of
     *
     * @return array An array of group identifiers
     */
    public function getGroups()
    {
        $groups = [];

        return $groups;
    }

    /**
     * Checks if the user is part of a group
     *
     * @param bool $strict If true, the user needs to be part of all provided group(s), not just one.
     * @return bool
     */
    public function hasGroup($group, $strict = false)
    {
        $groups = (array) $group;

        if($strict) {
            $result = !array_diff($groups, $this->getGroups());
        } else {
            $result = (bool) array_intersect($this->getGroups(), $groups);
        }

        return $result;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text password will be salted, encoded, and
     * then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Verify the password
     *
     * @param string $password The plain-text password to verify
     * @return bool Returns TRUE if the plain-text password and users hashed password, or FALSE otherwise.
     */
    public function verifyPassword($password)
    {
        return WP::wp_check_password( $password, $this->getPassword(), $this->getId());
    }

    /**
     * Returns the user parameters
     *
     * @return array The parameters
     */
    public function getParameters()
    {
        return new Library\ObjectConfig(WP::get_user_meta( $this->getId()));
    }

    /**
     * The user has been successfully authenticated
     *
     * @param  boolean $strict If true, checks if the user has been authenticated for this request explicitly
     * @return boolean True if the user is not logged in, false otherwise
     */
    public function isAuthentic($strict = false)
    {
        return $strict ?: $this->getId() == WP::get_current_user_id();
    }

    /**
     * Checks whether the user account is enabled.
     *
     * @return Boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Checks whether the user credentials have expired.
     *
     * @return Boolean
     */
    public function isExpired()
    {
        return (bool) $this->activation;
    }

    /**
     * Sets the user as authenticated for the request
     *
     * @return $this
     */
    public function setAuthentic()
    {
        $this->authentic = true;
        return $this;
    }

    /**
     * Get an user parameter
     *
     * @param string $name The parameter name
     * @param   mixed   $value      Default value when the parameter doesn't exist
     * @return  mixed   The value
     */
    public function get($name, $default = null)
    {
        $result = $this->getParameters()->get($name, $default);
        return $result;
    }

    /**
     * Set an user parameter
     *
     * @param string $name The parameter name
     * @param  mixed $value The parameter value
     * @return ModelEntityUser
     */
    public function set($name, $value)
    {
        $this->getParameters()->set($name, $value);
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
        $result = $this->getParameters()->has($name);
        return $result;
    }

    /**
     * Removes an user parameter
     *
     * @param string $name The parameter name
     * @return ModelEntityUser
     */
    public function remove($name)
    {
        $this->getParameters()->remove($name);
        return $this;
    }

    /**
     * Check if the user is equal
     *
     * @param  Library\UserInterface $user
     * @return Boolean
     */
    public function equals(Library\ObjectInterface $user)
    {
        if($user instanceof Library\UserInterface)
        {
            if($user->getEmail() == $this->getEmail())
            {
                if($user->getPassword() == $this->getPassword()) {
                    return true;
                }
            }
        }

        return false;
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
        return WP::user_can( $this->getId(), $action);
    }

    /**
     * Return an associative array containing the user data.
     *
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();

        $data['username']   = $this->getUsername();
        $data['roles']      = $this->getRoles();
        $data['groups']     = $this->getGroups();
        $data['parameters'] = $this->getParameters()->toArray();
        $data['expired']    = $this->isExpired();
        $data['authentic']  = $this->isAuthentic();

        $data = array_intersect_key($data, $this->_fields);

        return $data;
    }
}

