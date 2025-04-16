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
 * Error Controller Permission
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class ControllerPermissionUser extends Library\ControllerPermissionAbstract
{
    public function canAdd()
    {
        return false;
    }

    public function canEdit()
    {
        return false;
    }

    public function canDelete()
    {
        return false;
    }

    public function canAdmin()
    {
        return false;
    }

    public function canManage()
    {
        return false;
    }
}