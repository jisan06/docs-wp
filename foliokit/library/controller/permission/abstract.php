<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Library;

/**
 * Abstract Controller Permission
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Controller\Permission
 */
abstract class ControllerPermissionAbstract extends ObjectMixinAbstract implements ControllerPermissionInterface
{
    /**
     * Permission handler for render actions
     *
     * @return  boolean  Return TRUE if action is permitted. FALSE otherwise.
     */
    public function canRender()
    {
        return ($this->getMixer() instanceof ControllerViewable);
    }

    /**
     * Permission handler for read actions
     *
     * Method returns TRUE iff the controller implements the ControllerModellable interface.
     *
     * @return  boolean Return TRUE if action is permitted. FALSE otherwise.
     */
    public function canRead()
    {
        return ($this->getMixer() instanceof ControllerModellable);
    }

    /**
     * Permission handler for browse actions
     *
     * Method returns TRUE iff the controller implements the ControllerModellable interface.
     *
     * @return  boolean  Return TRUE if action is permitted. FALSE otherwise.
     */
    public function canBrowse()
    {
        return ($this->getMixer() instanceof ControllerModellable);
    }

    /**
     * Permission handler for count actions
     *
     * Method returns the results of canBrowse()
     *
     * @return  boolean  Return TRUE if action is permitted. FALSE otherwise.
     */
    public function canCount()
    {
        return $this->canBrowse();
    }

    /**
     * Permission handler for add actions
     *
     * Method returns TRUE iff the controller implements the ControllerModellable interface and the user is authentic
     * and the account is enabled.
     *
     * @return  boolean  Return TRUE if action is permitted. FALSE otherwise.
     */
    public function canAdd()
    {
        if($this->getMixer() instanceof ControllerModellable)
        {
            $user = $this->getUser();
            if ($user->isAuthentic() && $user->isEnabled()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Permission handler for edit actions
     *
     * Method returns TRUE iff the controller implements the ControllerModellable interface and the user is authentic
     * and the account is enabled.
     *
     * @return  boolean  Return TRUE if action is permitted. FALSE otherwise.
     */
    public function canEdit()
    {
        if($this->getMixer() instanceof ControllerModellable)
        {
            $user = $this->getUser();
            if ($user->isAuthentic() && $user->isEnabled()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Permission handler for delete actions
     *
     * Method returns true of the controller implements ControllerModellable interface and the user is authentic.
     *
     * @return  boolean  Returns TRUE if action is permitted. FALSE otherwise.
     */
    public function canDelete()
    {
        if($this->getMixer() instanceof ControllerModellable)
        {
            $user = $this->getUser();
            if ($user->isAuthentic() && $user->isEnabled()) {
                return true;
            }
        }

        return false;
    }
}