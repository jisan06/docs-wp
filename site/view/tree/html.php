<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;

class ViewTreeHtml extends ViewListHtml
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors' => [
                'decoratable' => ['decorators' => [
                    'com://site/easydoc/tree/sidebar.html'
                ]]
            ],
        ]);

        parent::_initialize($config);
    }

    protected function _fetchData(Library\ViewContextTemplate $context)
    {
        if ($this->hide_empty) {
            $this->getModel()->getState()->hide_empty = true;
        }

        parent::_fetchData($context);
    }
}
