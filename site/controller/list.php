<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

class ControllerList extends Base\ControllerModel
{
    /**
     * Model object or identifier (com://APP/COMPONENT.model.NAME)
     *
     * @var	string|object
     */
    protected $_model;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        // Set the model identifier
        $this->_model = $config->model;

        $this->addCommandCallback('before.delete', '_checkDocumentCount');
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'toolbars'  => ['list'],
            'formats'   => ['json', 'rss'],
            'model'     => 'com://site/easydoc.model.categories',
            'query_filters' => [
                'search_filter' => ['dmfilter' => ['filter' => 'string', 'name' => 'filter']] // filter query parameter will get filtered and pushed to options as a search filter option
            ],
            'behaviors' => [
                'hideable',
                'persistable',
                'restrictable' => [
                    'actions' => [
                        'edit',
                        'delete',
                        'add',
                        'upload',
                        'edit_document',
                        'delete_document',
                        'edit_category',
                        'delete_category',
                        'add_category'
                    ],
                    'tables' => [
                        'documents',
                        'categories'
                    ]
                ],
                // 'findable',
                'organizable',
                // 'sluggable',
                'com://site/easydoc.controller.behavior.filterable' => [
                    'options' => [
                        'sort'            => 'sort_documents',
                        'sort_categories' => 'sort_categories',
                        'tag'             => 'tags'
                    ]
                ]
            ]
        ]);

        parent::_initialize($config);
    }

    public function getRequest()
    {
        if(!$this->_request instanceof ControllerRequestInterface)
        {
            $request = parent::getRequest();
            $query = $request->getQuery();

            if (!isset($query->slug) && isset($query->path)) {
                $query->slug = array_pop(explode('/', $query->path));
            }

            if (!$query->uuid && $query->page_category) $query->uuid = $query->page_category;
        }

        return $this->_request;
    }

    /**
     * If the user is searching through multiple categories or a category other than the one in the URL redirect to root
     *
     * @param Library\ControllerContextInterface $context
     * @return bool
     */
    protected function _beforeRender(Library\ControllerContextInterface $context)
    {
        $query  = $context->request->query;
        $filter = $query->filter;

        // searching for something
        if (!empty($filter) &&
            (!empty($filter['reset']) || !empty($filter['category']) || !empty($filter['search']) || !empty($filter['created_on_from']) || !empty($filter['created_on_to']))
        ) {
            $categories = !empty($filter['category']) ? $filter['category'] : [];
            $categories = is_array($categories) ? $categories : [$categories];
            $route      = [];

            if (count($categories) === 1 && (!$this->getModel()->getState()->isUnique() || $categories[0] !== $this->getModel()->fetch()->id))
            {
                $model = clone $this->getModel();
                $slug = $model->reset()->slug(null)->id($categories[0])->fetch()->slug;

                $this->getModel()->getState()->id = null;

                $route = ['slug' => $slug, 'filter' => $query->filter];
            }

            if ($route) {
                $route = $this->getView()->getRoute($route, true, false);
                $context->response->setRedirect($route);

                return false;
            }
        }
    }

    /**
     *
     * @param Library\ControllerContextInterface $context
     */
    protected function _beforeBrowse(Library\ControllerContextInterface $context)
    {
        $query = $context->request->query;

        if ($query->page_category)
        {
            $model = clone $this->getModel();
            $model->reset()->getState()->reset();

            $category = $model->uuid($query->uuid)->fetch();

            $model->reset()->getState()->reset();

            $page_category = $model->uuid($query->page_category)->fetch();

            if (!$category->hasAncestor($page_category)) {
                throw new Library\ControllerExceptionResourceNotFound('Category not found');
            }
        }
    }

    /**
     * The created_by query parameter coming from the menu item is meant for documents.
     *
     * Temporarily unset it here until afterBrowse
     *
     * @param Library\ControllerContextInterface $context
     */
     /*protected function _beforeBrowse(Library\ControllerContextInterface $context)
     {
         $query    = $context->request->query;

         if ($query->created_by)
         {
             $menu = JFactory::getApplication()->getMenu()->getActive();

             if ($menu && isset($menu->query['created_by'])) {
                 $context->cache_created_by = $query->created_by;

                 $query->created_by = null;
             }
         }
     }*/

    /**
     * Restores created_by parameter in the request
     *
     * @param Library\ControllerContextInterface $context
     */
    /*protected function _afterBrowse(Library\ControllerContextInterface $context)
    {
        if ($context->cache_created_by) {
            $context->request->query->created_by = $context->cache_created_by;
        }
    }*/

    /**
     * Halts the delete if the category has documents attached to it.
     *
     * Also makes sure subcategories are deleted correctly when both
     * they and their parents are in the rowset to be deleted.
     *
     * @param KDispatcherContextInterface $context
     * @throws Library\ControllerExceptionActionFailed
     */
    protected function _checkDocumentCount(Library\ControllerContextInterface $context)
    {
        $data = $this->getModel()->documents_count(true)->fetch();

        if ($count = $data->_documents_count)
        {
            $message = $this->getObject('translator')->choose([
                'This category or its children has a document attached. You first need to delete or move it before deleting this category.',
                'This category or its children has {count} documents attached. You first need to delete or move them before deleting this category.'
            ], $count, ['count' => $count]);

            if ($context->getRequest()->getFormat() === 'html') {
                $context->getResponse()->addMessage($message, Library\ControllerResponse::FLASH_ERROR);
                $context->response->setRedirect($this->getRequest()->getReferrer());

                return false;
            } else {
                throw new Library\ControllerExceptionActionFailed($message);
            }
        }

        /*
         * Removes the child categories from the rowset since they will be deleted by their parent.
         * Otherwise rowset gets confused when it tries to delete a non-existant row.
         */
        if ($data instanceof Library\ModelEntityInterface)
        {
            $to_be_deleted = [];

            // PHP gets confused if you extract a row and then continue iterating on the rowset
            $iterator = clone $data;
            foreach ($iterator as $entity)
            {
                if (in_array($entity->id, $to_be_deleted)) {
                    $data->remove($entity);
                }

                foreach ($entity->getDescendants() as $descendant) {
                    $to_be_deleted[] = $descendant->id;
                }
            }
        }
    }
}
