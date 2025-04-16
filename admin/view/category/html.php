<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Library;

class ViewCategoryHtml extends ViewHtml
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors' => ['com:easydoc.view.behavior.permissible', 'com:easydoc.view.behavior.notifiable'],
            'decorator' => 'foliokit'
        ]);

        parent::_initialize($config);
    }

    protected function _fetchData(Library\ViewContextTemplate $context)
    {
        parent::_fetchData($context);

        $context->data->parent = null;
        $context->data->parent = $this->getModel()->fetch()->getParent();

        $category = $context->data->category;
        $ignored_parents = [];

        if ($category->id) {
            $ignored_parents[] = $category->id;
            foreach ($category->getDescendants() as $descendant) {
                $ignored_parents[] = $descendant->id;
            }
        }

        $context->data->category_filter = [];
        $context->data->ignored_parents = $ignored_parents;
    }
}
