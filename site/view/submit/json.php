<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;

class ViewSubmitJson extends Library\ViewJson
{
    /**
     * Returns the JSON data
     *
     * It converts relative URLs in the content to relative before returning the result
     *
     * @return array
     */
    protected function _fetchData(Library\ViewContext $context)
    {
        if ($content = $this->getContent()) {
            $context->content = $content;
        } else {
            parent::_fetchData($context);
        }
    }
}
