<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\EasyDoc;

/**
 * Submit controller permissions
 */
class ControllerPermissionSubmit extends EasyDoc\ControllerPermissionDocument
{
    public function canBrowse()
    {
        return false;
    }

    /**
     * Submit view is meant to be used with new items only
     *
     * @return bool
     */
    public function canRead()
    {
        return !($this->getModel()->getState()->isUnique());
    }

    public function canEdit()
    {
        return false;
    }

    public function canDelete()
    {
        return false;
    }
}