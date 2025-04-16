<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class DatabaseTableDocuments extends Library\DatabaseTableAbstract
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors' => [
                'lockable',
                'creatable',
                'modifiable',
                'sluggable',
                'identifiable',
                'hittable',
                'parameterizable',
                'invalidatable',
                'orderable',
                'routable',
                'com:easydoc.database.behavior.document.permissible',
                'com:easydoc.database.behavior.document.notifiable'
            ],
            'column_map' => [
                'parameters' => 'params',
                'touched_on' => 'GREATEST(tbl.created_on, tbl.modified_on)'
            ],
            'filters' => [
                'parameters'   => ['json'],
                'title'        => ['trim'],
                'storage_type' => ['com://admin/easydoc.filter.identifier'],
                'summary'      => ['com://admin/easydoc.filter.summary'],
                'description'  => ['trim', 'com:base.filter.html']
            ]
        ]);

        parent::_initialize($config);
    }
}
