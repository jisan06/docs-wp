<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

class ViewDocumentsHtml extends Base\ViewHtml
{
    protected function _fetchData(Library\ViewContextTemplate $context)
    {
        if ($this->getLayout() === 'attachments') {
            $this->setDecorator('foliokit');
        }

        if ($this->getModel()->getState()->category)
        {
            $category = $this->getObject('com://admin/easydoc.model.categories')
                ->id($this->getModel()->getState()->category)->fetch();

            $context->data->category = $category;
        }

        $state = $this->getModel()->getState();

        $context->data->category_count = $this->getObject('com://admin/easydoc.model.categories')->access($state->access)->documents_access(true)->count();
        $context->data->document_count = $this->getObject('com://admin/easydoc.model.documents')->access($state->access)->count();
        $context->data->can_create_tag = $this->getObject('com://admin/easydoc.model.configs')->fetch()->can_create_tag;
        $context->data->access         = $this->getModel()->getState()->access;

        parent::_fetchData($context);
    }
}
