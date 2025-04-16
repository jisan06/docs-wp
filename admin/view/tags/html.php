<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Library;

 class ViewTagsHtml extends ViewHtml
 {
     protected function _fetchData(Library\ViewContextTemplate $context)
     {
         $context->data->tag_count = $this->getObject('com:easydoc.model.tags')->count();
         
         parent::_fetchData($context);
     }
 }
