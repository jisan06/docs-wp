<?php
/**
 * @package     Foliokit Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Migrator;

use EasyDocLabs\Component\Base;

class ControllerPermissionImport extends Base\ControllerPermissionAbstract
{
    /**
     * Only people who are able to manage EXTman can see it
     *
     * @return bool
     */
    public function canRender()
    {
        return $this->canManage();
    }

    /**
     * Only people who are able to manage EXTman can run it
     *
     * @return bool
     */
    public function canRun()
    {
        return $this->canManage();
    }
}
