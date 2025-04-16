<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;

class ViewCategoryHtml extends ViewHtml
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors'  => ['com:easydoc.view.behavior.permissible', 'com:easydoc.view.behavior.notifiable'],
            'decorator'  => $config->layout === 'form' ? 'foliokit' : 'joomla'
        ]);

        parent::_initialize($config);
    }

    protected function _fetchData(Library\ViewContextTemplate $context)
    {
        parent::_fetchData($context);

        $context->data->parent = $this->getModel()->fetch()->getParent();

        $category = $context->data->category;
        $ignored_parents = [];

        if ($category->id) {
            $ignored_parents[] = $category->id;
            foreach ($category->getDescendants() as $descendant) {
                $ignored_parents[] = $descendant->id;
            }
        }

        $context->data->ignored_parents = $ignored_parents;

        if (!$category->id && isset($category->parent_id)) {
            $context->data->parent = $this->getObject('com://site/easydoc.model.categories')->id($category->parent_id)->fetch();
        }

        $user = $this->getObject('user');

        $category_filter['enabled'] = $user->isAdmin() ? null : 1;

		$options = $this->getOptions();

		if ($parent = $options->parent_category)
		{
			if ($options->show_parent_children)
			{
				$category_filter['parent_id']    = $parent;
				$category_filter['include_self'] = true;
			}
			else $category_filter['id'] = $parent;
		}

        $context->data->category_filter = $category_filter;
    }
}
