<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

class ControllerNotification extends Base\ControllerModel
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['permission' => 'com:easydoc.controller.permission.notification']);

        parent::_initialize($config);
    }
}