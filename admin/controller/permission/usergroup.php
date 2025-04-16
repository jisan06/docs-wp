<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

/**
 * Usergroup controller permissions
 */
class ControllerPermissionUsergroup extends ControllerPermissionConfigure
{
    public function canEdit()
    {
        if ($this->getModel()->fetch()->internal) {
            $result = !$this->isDispatched();
        } else {
            $result = parent::canEdit();
        }

        return $result;
    }
}
