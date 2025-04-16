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

class ViewHtml extends Base\ViewHtml
{
    protected function _fetchData(Library\ViewContextTemplate $context)
    {
        // $this->getObject('translator')->load('com:files');

        parent::_fetchData($context);
    }
}
