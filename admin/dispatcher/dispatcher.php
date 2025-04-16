<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\EasyDoc;
use EasyDocLabs\Library;

class Dispatcher extends EasyDoc\Dispatcher
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors' => [
                'com:migrator.dispatcher.behavior.migratable'
            ]
        ]);
        
        parent::_initialize($config);
    }
}
