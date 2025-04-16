<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

class ViewDocumentHtml extends Base\ViewHtml
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'decorator' => 'foliokit'
        ]);

        parent::_initialize($config);
    }

    protected function _fetchData(Library\ViewContextTemplate $context)
    {
        $context->data->category_filter  = [];
        $context->data->show_owner_field = true;
        $context->data->can_create_tag   = $this->getObject('com://admin/easydoc.model.configs')
                                                ->fetch()->can_create_tag;
                                                
        parent::_fetchData($context);
        
        $context->data->document->setProperty('automatic_humanized_titles', $this->getObject('com://admin/easydoc.model.entity.config')->automatic_humanized_titles);
    }
}
