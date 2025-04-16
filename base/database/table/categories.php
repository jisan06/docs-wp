<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class DatabaseTableCategories extends Library\DatabaseTableAbstract
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $clear_permissions = function(Library\DatabaseContextInterface  $context) {
            $this->getObject('easydoc.users')->clearPermissions();
        };

        $this->addCommandCallback('after.insert', $clear_permissions);
        $this->addCommandCallback('after.update', $clear_permissions);
        $this->addCommandCallback('after.delete', $clear_permissions);
    }

    protected function  _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'relation_table' => 'easydoc_category_relations',
            'behaviors'      => [
                'lockable',
                'sluggable',
                'creatable',
                'modifiable',
                'identifiable',
                'com:easydoc.database.behavior.category.orderable',
                'parameterizable',
                'nestable' => ['relation_table' => 'easydoc_category_relations'],
                'invalidatable',
                'routable',
                'categorygroupinheritable',
                'categorygrouprelatable',
                'documentgroupinheritable',
                'documentgrouprelatable',
                'permissible',
                'notifiable'
            ],
            'column_map' => [
                'parameters' => 'params'
            ],
            'filters'        => [
                'parameters'  => ['json'],
                'title'       => ['trim'],
                'description' => ['trim', 'com:base.filter.html']
            ]
        ]);

        parent::_initialize($config);
    }
}
