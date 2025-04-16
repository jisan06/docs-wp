<?php
/**
 * Foliokit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Activities;

use EasyDocLabs\Library;

/**
 * Activity Controller Permission.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Activities
 */
class ControllerPermissionActivity extends Library\ControllerPermissionAbstract
{
    public function canAdd()
    {
        return !$this->isDispatched(); // Do not allow activities to be added if the controller is dispatched.
    }

    public function canEdit()
    {
        return false; // Do not allow activities to be edited.
    }

    public function canPurge()
    {
       return !$this->isDispatched() || $this->canDelete();
    }
}
