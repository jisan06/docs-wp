<?php
/**
 * Foliokit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Activities;

/**
 * Resource Controller Permission.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Activities
 */
class ControllerPermissionResource extends ControllerPermissionActivity
{
    public function canEdit()
    {
        return $this->canAdd();
    }

    public function canDelete()
    {
        return $this->canAdd();
    }
}