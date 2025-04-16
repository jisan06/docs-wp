<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

class ViewCategoriesHtml extends Base\ViewHtml
{
    protected function _fetchData(Library\ViewContextTemplate $context)
    {
        $context->data->access         = $this->getModel()->getState()->access;
        $context->data->category_count = $this->getObject('com:easydoc.model.categories')->access($context->data->access)->count();

        parent::_fetchData($context);

        if ($this->getModel()->getState()->parent_id)
        {
            $parent = $this->getObject('com://admin/easydoc.model.categories')
                ->id($this->getModel()->getState()->parent_id)->fetch();

            $context->data->parent = $parent;
        }
    }
}
