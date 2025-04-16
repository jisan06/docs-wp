<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;

class ViewListJson extends ViewJson
{
    /**
     * Returns the JSON output
     *
     * Overridden to return both child categories and documents as well as category information
     * for the category in menu item
     *
     * @return array
     */
    protected function _fetchData(Library\ViewContext $context)
    {
        $output = new \ArrayObject(array(
            'jsonapi' => array('version' => $this->_version),
            'links'   => array('self' => $this->getUrl()->toString()),
            'data'    => array()
        ));

        $model = $this->getModel();
        $url   = $this->getUrl();

        $state = $model->getState();

        $categories_model = $this->getObject('com://site/easydoc.model.categories')
                                ->level(1)
                                ->enabled($state->enabled)
                                ->hide_empty($this->hide_empty)
                                ->documents_count($state->documents_count)
                                ->sort($this->getOption('sort_categories', 'title'))
                                ->access($state->access)
                                ->limit(0);

        if ($state->isUnique())
        {
            $category = $this->getModel()->fetch()->getIterator()->current();

            if ($this->getOption('show_subcategories', true)) {
                $category->_subcategories = $categories_model->parent_id($category->id)->fetch();
            } else {
                $category->_subcategories = [];
            } 

            $tag = Library\ObjectConfig::unbox($this->getOption('tags'));

            $model = $this->getObject('com://site/easydoc.controller.document')
                ->enabled($state->enabled)
                ->status($state->status)
                ->category($category->id)
                ->limit($state->limit)
                ->offset($state->offset)
                ->tag($tag)
                ->sort($state->sort)
                ->direction($state->direction)
                ->getModel();

            $total     = $model->count();
            $documents = $model->fetch();

            $limit  = (int) $model->getState()->limit;
            $offset = (int) $model->getState()->offset;

            if ($limit && $total-($limit + $offset) > 0)
            {
                $output['links']['next'] = [
                    'href' => $url->setQuery(array('offset' => $limit+$offset), true)->toString()
                ];
            }

            if ($limit && $offset && $offset >= $limit)
            {
                $output['links']['prev'] = [
                    'href' => $url->setQuery(array('offset' => max($offset-$limit, 0)), true)->toString()
                ];
            }

            $output['meta']['total'] = $total;

            $category->_documents = $documents;

            $output['data'][] = $this->_includeResource($category, [], false);
        }
        else
        {
            $categories = $categories_model->parent_id(0)->fetch();

            $output['meta']['total'] = 0;

            $output['data'] = $this->_includeCollection($categories, [], false);
        }
    
        if ($this->_included_resources) {
            $output['included'] = array_values($this->_included_resources);
        }
        
        $context->content = $output;
    }

    protected function _getCategoryRelationships(Library\ModelEntityInterface $category)
    {
        $data = parent::_getCategoryRelationships($category);

        if ($subcategories = $category->_subcategories)
        {   
            $data['subcategories'] = [];
            
            foreach ($subcategories as $subcategory) {
                $data['subcategories'][] = $this->_getEntityRelationship($subcategory);
            }
        }

        if ($documents = $category->_documents)
        {
            $data['documents'] = [];
            
            foreach ($documents as $document) {
                $data['documents'][] = $this->_getEntityRelationship($document);
            }
        }

        return $data;
    }
}
