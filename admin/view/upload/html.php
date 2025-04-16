<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Library;
use EasyDocLabs\WP;

class ViewUploadHtml extends ViewHtml
{
    protected function _initialize(Library\ObjectConfig $config)
    {
		$config->append([
			'layout'     => 'default',
			'auto_fetch' => false,
			'decorator'  => 'foliokit'
		]);

		if (!WP::is_admin()) {
			$config->append(['behaviors' => ['com://site/easydoc.view.behavior.pageable']]);
		}

        parent::_initialize($config);
    }

    protected function _fetchData(Library\ViewContextTemplate $context)
    {
        // Load administrator language file for messages
        $this->getObject('translator')->load('com://admin/easydoc');
        $this->getObject('translator')->load('com:files');

		$context->data->show_uploader = !Library\ObjectConfig::unbox($context->data->paths);

        if (!$context->data->selected_category) {
            $context->data->selected_category = null;
        }

        if ($context->data->folder)
        {
            $category = $this->getObject('com://admin/easydoc.model.categories')->folder($context->data->folder)->fetch();

            if (count($category) === 1 && !$category->isNew()) {
                $context->data->selected_category = $category->id;
            }
        }

        $category_filter = [];

		$user = $this->getObject('user');

        if ($this->isOptionable())
        {
			$category_filter['enabled'] = $user->isAdmin() ? null : 1;
			$options                    = $this->getOptions();

			if ($parent = $options->parent_category)
			{
				if ($options->show_parent_children)
				{
					$category_filter['parent_id']    = $parent;
					$category_filter['include_self'] = true;
				}
				else $category_filter['id'] = $parent;
			}
        }

		$context->data->category_filter  = $category_filter;
		$context->data->show_owner_field = $user->canManage();
		$context->data->tag_count        = $this->getObject('com://admin/easydoc.model.tags')->count();
		$context->data->can_create_tag   = $this->getObject('com://admin/easydoc.model.configs')->fetch()->can_create_tag;
		$context->data->hide_tag_field   = $context->data->tag_count == 0 && !$context->data->can_create_tag;
        
        $context->data->automatic_humanized_titles = $this->getObject('com://admin/easydoc.model.entity.config')->automatic_humanized_titles;

        parent::_fetchData($context);
    }
}
