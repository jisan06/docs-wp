<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;

class ViewSubmitHtml extends ViewHtml
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->auto_fetch = false;

        parent::_initialize($config);
    }

    protected function _fetchData(Library\ViewContextTemplate $context)
    {
        if ($this->getLayout() != 'success')
        {
            $this->getObject('translator')->load('com:files');

            $options = $this->getOptions();

            $context->data->document = $this->getModel()->fetch();

            //Get selected categories
            $context->data->categories = $options->get('category_id');

            $categories = Library\ObjectConfig::unbox($context->data->categories);

            if ($categories == 0) {
                $categories = [];
            } else {
                $categories = (array) $categories;
            }

            //Get the level so we know whether to count the children or not
            $level = empty($categories) || $options->get('category_children') ? null : [0];

            $count = empty($categories) ? 0 : count($categories);

            //If there is only a single category for this user then we do not
            //display the category
            $context->data->show_categories = $count != 1 || is_null($level);
            $context->data->level = $level;

            //set categories to 0 if nothing is selected. I am using this to show all categories
            if($count == 0) {
                $context->data->categories = 0;
            } elseif ($count == 1) {
                $context->data->categories = current($categories);
            }

            // Pass block options to be forwarded to endpoint
            $context->data->query_options = $this->getQueryOptions();
        }

        parent::_fetchData($context);
    }
}
