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
 * Installer Controller Permission
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class ControllerPermissionInstaller extends Library\ControllerPermissionAbstract
{
    public function canInstall()
    {
        return $this->getObject('user')->authorise('activate_plugins');
    }

    public function canUpdate()
    {
        return $this->getObject('user')->authorise('update_plugins');
    }

    public function canUninstall()
    {
        return $this->getObject('user')->authorise('delete_plugins');
    }
}