<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

class ControllerEditor extends Base\ControllerView
{
    public function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['view' => 'com:easydoc.view.editor.html']);

        parent::_initialize($config);
    }

    public function getView()
    {
        $view = parent::getView();

        $field = $this->getRequest()->getQuery()->field;

        if (!$view->getField() && ($field = $this->getRequest()->getQuery()->field)) {
            $view->setField($field);
        }
        
        return $view;
    }
}
