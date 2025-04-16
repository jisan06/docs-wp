<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;

class ViewFlatHtml extends ViewHtml
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'auto_fetch' => false
        ]);

        parent::_initialize($config);
    }

    public function isCollection()
    {
        return true;
    }

    public function getLayout()
    {
        $layout = parent::getLayout();

        if ($layout == 'default') $layout = 'list';

        return $layout;
    }

    protected function _fetchData(Library\ViewContextTemplate $context)
    {
		$context->data->documents     = $this->getModel()->fetch();
		$context->data->total         = $this->getModel()->count();
		$context->data->query_options = $this->getQueryOptions();

        foreach ($context->data->documents as $document) {
            $this->prepareDocument($document);
        }

        parent::_fetchData($context);

        $context->parameters->total = $this->getModel()->count();
        $context->data->show_action_buttons = $this->getOption('show_action_buttons');

        $this->_setSearchFilterData($context);
    }

    protected function _setSearchFilterData(Library\ViewContextTemplate $context)
    {
        $context->data->filter = $this->getObject('lib:http.message.parameters', ['parameters' => $this->getModel()->getState()->getValues()]);

        $options    = $this->getOptions();
        $filter     = $context->data->filter;
        $owner      = !empty($options->created_by) ? $options->created_by : null;
        $categories = !empty($options->category) ? $options->category : [];
        $children   = isset($options->category_children) ? $options->category_children : true;

        if (!empty($options->own) || empty($owner) || count($owner) <= 1) {
            $options->set('show_owner_filter', false);
        }

        if (!$this->getObject('com://admin/easydoc.model.entity.config')->connectAvailable()) {
            $options->set('show_content_filter', false);
        }
        elseif ($filter->search_contents === null) {
            $filter->search_contents = true;
        }

        $category_filter = [
            'page'         => $this->getModel()->getState()->page,
            'access'       => $this->getObject('user')->getRoles(),
            'current_user' => $this->getObject('user')->getId(),
            'enabled'      => true
        ];

        if ($categories)
        {
            if ($children) {
                $category_filter['parent_id'] = $categories;
                $category_filter['include_self'] = true;
            } else {
                $category_filter['id'] = $categories;
            }
        }

        $context->data->filter_toggled = ($context->parameters->total > $this->getModel()->getState()->limit)
        || (!empty($filter->search)
            || (!empty($filter->category) && $filter->category != $categories)
            || (!empty($filter->created_by) && $filter->created_by != $owner));

        $context->data->category_filter = $category_filter;
    }
}
