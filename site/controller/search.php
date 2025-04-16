<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

class ControllerSearch extends Base\ControllerModel
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'model' => 'documents',
            'behaviors' => [
                'com://site/easydoc.controller.behavior.filterable' => [
                    'options' => ['sort' => 'sort_documents']
                ]
            ]
        ]);
        
        parent::_initialize($config);
    }
}
