<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Component\Tags;
use EasyDocLabs\Library;

class ControllerTag extends Tags\ControllerTag
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors' => [
                'editable',
                'restrictable' => [
                    'redirect_url' => 'admin.php?page=easydoc-settings'
                ],
                'persistable'
            ],
            'formats'   => ['json'],
            'toolbars'  => [
                'menubar',
                'com:easydoc.controller.toolbar.tag'
            ]
        ]);

        parent::_initialize($config);
    }
}
