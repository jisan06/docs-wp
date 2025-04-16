<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

/**
 * Connect controller permission
 *
 * @author Arunas Mazeika <https://github.com/amazeika>
 */
class ControllerPermissionConnect extends ControllerPermissionYesman
{
    public function canRender()
    {
        return true;
    }
}