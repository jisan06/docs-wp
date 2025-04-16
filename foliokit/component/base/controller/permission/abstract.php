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

/**
 * Abstract Controller Permission
 *
 * @author  Israel Canasa <http://github.com/raeldc>
 * @package EasyDocLabs\Component\Base
 */
abstract class ControllerPermissionAbstract extends Library\ControllerPermissionAbstract
{
    /**
     * Permission handler for add actions
     *
     * Method returns TRUE iff the controller implements the KControllerModellable interface and the user is authentic
     * and the account is enabled.
     *
     * @return  boolean  Return TRUE if action is permitted. FALSE otherwise.
     */
    public function canAdd()
    {
        return $this->getUser()->authorise('edit_posts');
    }

    /**
     * Permission handler for edit actions
     *
     * Method returns TRUE iff the controller implements the KControllerModellable interface and the user is authentic
     * and the account is enabled.
     *
     * @return  boolean  Return TRUE if action is permitted. FALSE otherwise.
     */
    public function canEdit()
    {
        return $this->getUser()->authorise('edit_posts');
    }

    /**
     * Permission handler for delete actions
     *
     * Method returns true of the controller implements KControllerModellable interface and the user is authentic.
     *
     * @return  boolean  Returns TRUE if action is permitted. FALSE otherwise.
     */
    public function canDelete()
    {
        return $this->getUser()->authorise('delete_posts');
    }

    /**
     * Check if user can perform administrative tasks such as changing configuration options
     *
     * @return  boolean  Can return both true or false.
     */
    public function canAdmin()
    {
        return $this->getObject('user')->authorise('manage_options');
    }

    /**
     * Check if user can can access a component in the administrator backend
     *
     * @return  boolean  Can return both true or false.
     */
    public function canManage()
    {
        return $this->getObject('user')->authorise('manage_options');
    }

    public function getMixableMethods($exclude = array())
    {
        if(!$this->_mixable_methods)
        {
            $methods = parent::getMixableMethods($exclude);

            $mixer = $this->getMixer();

            $overridden = [];

            foreach ($methods as $name => $method)
            {
                $overridden[$name] = function(...$arguments) use ($mixer, $name, $method)
                {
                    $is_restricted = $mixer->isRestrictable() && $mixer->isRestricted() && $mixer->isRestrictedAction($name);

                    return $is_restricted ? false : $method->{$name}(...$arguments);
                };
            }

            $this->_mixable_methods = array_merge($this->_mixable_methods, $overridden);
        }

        return $this->_mixable_methods;
    }
}