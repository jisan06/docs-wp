<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Component\Base;

/**
 * Tag controller permissions
 */
class ControllerPermissionConfigure extends Base\ControllerPermissionAbstract
{
    public function canAdd()
    {
        return $this->canDelete();
    }

    public function canEdit()
    {
        return $this->canDelete();
    }

    public function canRender()
    {
        return $this->canDelete();
    }

    public function canDelete()
    {
        return $this->getMixer()->getUser()->canConfigure();
    }
}