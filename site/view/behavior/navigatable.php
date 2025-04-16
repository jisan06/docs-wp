<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;

class ViewBehaviorNavigatable extends Library\ViewBehaviorAbstract
{
    protected function _beforeRender(Library\ViewContextTemplate $context)
    {
        // @todo Implement menu-based configuration
        // $query  = $this->getActiveMenu()->query;

        $view = $this->getMixer();

        if ($view->getName() === 'tree' && $view->getLayout() !== 'form')
        {
            $state   = $this->getModel()->getState();
            $data    = $this->_getSidebarData($context);


            if (in_array($this->getLayout(), ['list', 'table', 'gallery']) && !$state->isUnique())
            {
                /*
                 * Automatically render the first category for top level tree view
                 * Otherwise the content pane will be empty and the page will only display the sidebar
                 *
                 * If search is enabled no such redirect is necessary, we just hide the top-level categories
                 */
                if (!$this->getOption('show_document_search') && empty($this->getOption('search_filter')))
                {
                    $model = $this->getObject('com://site/easydoc.model.categories');
                    $first = $model->setState($data['state'])->limit(1)->fetch();

                    // Ensure there is a category
                    if (!$first->isNew())
                    {
                        $category_link = $this->getObject('router')->generate($first, ['view' => 'tree']);

                        $this->getObject('response')->setRedirect($category_link)->send();
                    }
                }
                else $this->setOption('show_subcategories', false); // Search is visible, so content pane is not empty. We hide categories then
            }

            // Set tree config for use with sidebar tree view
            
            $context->data->merge($data);
        }
    }

    protected function _getSidebarData(Library\ViewContextTemplate $context)
    {
        $state    = $this->getModel()->getState();
        $selected = null;

        if ($this->getMixer()->getName() === 'document') {
            $selected = $this->getModel()->fetch()->easydoc_category_id;
        }
        else {
            $selected = $this->getModel()->fetch()->id;
        }

        $data = [
            'state'    => [
                'enabled'      => $state->enabled,
                'access'       => $state->access,
                'current_user' => $this->getObject('user')->getId(),
                'page'         => $state->page,
                'hide_empty'   => $state->hide_empty,
                'parent_id'    => $this->getOption('parent_category'),
                'include_self' => true,
                'sort'         => $this->getOption('sort_categories'),
                'direction'    => $this->getOption('direction_categories')
            ],
            'selected' => $selected
        ];

        return $data;
    }
}