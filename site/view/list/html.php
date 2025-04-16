<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\EasyDoc;
use EasyDocLabs\Library;

class ViewListHtml extends ViewHtml
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'auto_fetch' => false
        ]);

        parent::_initialize($config);
    }

    public function getLayout()
    {
        $layout = parent::getLayout();

        if ($layout == 'default') $layout = 'list';

        return $layout;
    }

    public function isCollection()
    {
        return true;
    }

    protected function _fetchData(Library\ViewContextTemplate $context)
    {
        $state = $this->getModel()->getState();

        //Category
        if ($this->getModel()->getState()->isUnique()) {
            $category = $this->getModel()->fetch();
        }
        else
        {
            $category = $this->getModel()->create();
            $category->title = $this->getOption('page_heading');
        }

        if ($state->isUnique() && $category->isNew()) {
            throw new EasyDoc\ControllerExceptionUnauthorizedCategory();
        }

        $tag = Library\ObjectConfig::unbox($this->getOption('tags'));

        //Subcategories
        if ($this->getOption('show_subcategories', true))
        {
            $subcategories = $this->getObject('com://site/easydoc.model.categories')
                ->level(1)
                ->parent_id($category->id)
                ->enabled($state->enabled)
                ->access($state->access)
                ->hide_empty($this->hide_empty)
                ->documents_count($state->documents_count)
                ->sort($this->getOption('sort_categories', 'title'))
                ->direction($this->getOption('direction_categories'))
                ->tag($tag)
                ->limit(0)
                ->fetch();
        }
        else $subcategories = [];

        $filter = $this->getOption('search_filter') ?? [];
        
        $filter = $this->getObject('lib:http.message.parameters', ['parameters' => Library\ObjectConfig::unbox($filter)]);

        $has_filter = false;

        foreach ($filter as $key => $value)
        {
            if (!empty($value))
            {
                $has_filter = true;
                break;
            }
        }

        //Documents
        if ($category->id || $has_filter)
        {
            if ($category->id) {
                $this->getOptions()->show_document_category = false;
            }

            //$document_category = $has_filter ? $filter->category : $category->id;
            $document_category          = $category->id;
            $document_category_children = $has_filter ? (isset($document_category) ? true : false) : false;
            $search_contents            = $filter->search_contents === null ? true : $filter->search_contents;
            $status                     = $this->status;

            $model = $this->getObject('com://site/easydoc.controller.document')
                ->enabled($state->enabled)
                ->status($status)
                ->limit($state->limit)
                ->offset($state->offset)
                ->sort($state->sort)
                ->direction($state->direction)
                ->access($state->access)
                ->created_by($filter->created_by)
                ->created_on_from($filter->created_on_from)
                ->created_on_to($filter->created_on_to)
                ->search($filter->search)
                ->search_by($this->getOption('search_by'))
                ->search_contents($search_contents)
                ->tag($tag)
                ->category($document_category)
                ->category_children($document_category_children)
                ->getModel();

            $total     = $model->count();
            $documents = $model->fetch();

            foreach ($documents as $document) {
                $this->prepareDocument($document);
            }
        }
        else
        {
            $total     = 0;
            $documents = [];
        }

        $context->data->category      = $category;
        $context->data->documents     = $documents;
        $context->data->total         = $total;
        $context->data->subcategories = $subcategories;
        $context->data->query_options = $this->getQueryOptions();
        $context->data->filter        = $filter;

        parent::_fetchData($context);

        $context->parameters->total = $total;

        $this->_setSearchFilterData($context);
    }

    protected function _setSearchFilterData(Library\ViewContext $context)
    {
        $options  = $this->getOptions();
        $category = $this->getModel()->fetch();
        $filter   = $context->data->filter;

        $owner = !empty($filter->created_by) ? $filter->created_by : null;

        if (!empty($filter->own) || empty($owner) || count($owner) <= 1) {
            $options->set('show_owner_filter', false);
        }

        if (!$this->getObject('com://admin/easydoc.model.entity.config')->connectAvailable()) {
            $options->set('show_content_filter', false);
        } elseif ($filter->search_contents === null) {
            $filter->search_contents = true;
        }

        // pre-select the current category if possible
        if (empty($filter->category) && $category->id) {
            $filter->category = [$category->id];
        }

        // Toggle the filters at all times in menu item root
        if ($category->id)
        {
            $context->data->filter_toggled = !empty($filter->search)
                || (!empty($filter->category) && $filter->category != [$category->id])
                || (!empty($filter->created_by) && $filter->created_by != $owner);
        }
        else $context->data->filter_toggled = true;

        $context->data->filter_group = 'dmfilter';

        $enabled = $this->getModel()->getState()->enabled;

        $context->data->category_filter = [
            'page'         => $this->getModel()->getState()->page,
            'access'       => $this->getObject('user')->getRoles(),
            'current_user' => $this->getObject('user')->getId(),
            'enabled'      => true
        ];
    }
}
