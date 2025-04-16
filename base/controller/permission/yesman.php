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
 * Controller permission to allow all CRUD actions
 *
 * @author Ercan Ozkaya <https://github.com/ercanozkaya>
 *
 */
class ControllerPermissionYesman extends Library\ControllerPermissionAbstract
{
    public function canAdmin()
    {
        return true;
    }

    /**
     * Permission handler for add actions
     *
     * Method returns TRUE iff the controller implements the Library\ControllerModellable interface
     *
     * @return  boolean  Return TRUE if action is permitted. FALSE otherwise.
     */
    public function canAdd()
    {
        return ($this->getMixer() instanceof Library\ControllerModellable);
    }

    /**
     * Permission handler for edit actions
     *
     * Method returns TRUE iff the controller implements the Library\ControllerModellable interface
     *
     * @return  boolean  Return TRUE if action is permitted. FALSE otherwise.
     */
    public function canEdit()
    {
        return ($this->getMixer() instanceof Library\ControllerModellable);
    }

    /**
     * Permission handler for delete actions
     *
     * Method returns true of the controller implements Library\ControllerModellable interface
     *
     * @return  boolean  Returns TRUE if action is permitted. FALSE otherwise.
     */
    public function canDelete()
    {
        return ($this->getMixer() instanceof Library\ControllerModellable);
    }
}