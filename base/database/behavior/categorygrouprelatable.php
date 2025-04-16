<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class DatabaseBehaviorCategoryGroupRelatable extends DatabaseBehaviorRelatable
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['property' => 'category_group_access', 'table' => 'category_group_access']);

        parent::_initialize($config);
    }


    protected function _afterUpdate(Library\DatabaseContextInterface $context)
    {
        if (isset($context->data['permissions']['usergroups']['view_category'])) {
            $context->data->{$this->_property} = $context->data['permissions']['usergroups']['view_category']; // Map POST data to relatable property
        }

        return parent::_afterUpdate($context);
    }
}
