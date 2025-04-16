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
 * @author  Israel Canasa <http://github.com/raeldc>
 * @package EasyDocLabs\Component\Base
 */
class ControllerPermissionError extends Library\ControllerPermissionAbstract
{
    public function canRender()
    {
        return true;
    }
}