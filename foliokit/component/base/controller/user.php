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
 * User Controller
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class ControllerUser extends ControllerModel
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'model' => 'com:base.model.users',
            'view'  => 'com:base.view.users.json'
        ]);

        parent::_initialize($config);
    }
}
