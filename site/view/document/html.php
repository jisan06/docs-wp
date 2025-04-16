<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;

class ViewDocumentHtml extends ViewHtml
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'decorator' => in_array($config->layout, ['form', 'preview']) ? 'foliokit' : 'wordpress'
        ]);

        parent::_initialize($config);
    }

    protected function _fetchData(Library\ViewContextTemplate $context)
    {
        $document = $this->getModel()->fetch();

        if ($this->getLayout() === 'form')
        {
            $this->getObject('translator')->load('com:files');

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

            $context->data->category_filter  = $category_filter;
            $context->data->show_owner_field = $user->canManage();
        }
        else
        {
            $this->prepareDocument($document);

            $context->data->query_options = $this->getQueryOptions();
        }

        parent::_fetchData($context);
    }
}
