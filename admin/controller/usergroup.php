<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

class ControllerUsergroup extends Base\ControllerModel
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'toolbars' => [
                'menubar',
                'restrictable' => [
                    'redirect_url' => 'admin.php?page=easydoc-settings'
                ],
                'com:easydoc.controller.toolbar.usergroup'
            ]
        ]);

        parent::_initialize($config);
    }
}