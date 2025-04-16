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

class ControllerFlat extends Base\ControllerModel
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'toolbars' => null,
            'formats'  => ['json', 'rss'],
            'model'    => 'com://site/easydoc.model.documents',
            'behaviors' => [
                'com://site/easydoc.controller.behavior.filterable' => [
                    'options' => ['sort' => 'sort_documents', 'tag' => 'tags']
                ],
                'restrictable' => [
                    'actions' => ['edit', 'delete', 'upload', 'delete_document', 'edit_document'],
                    'tables' => ['documents']
                ]
            ]
        ]);

        parent::_initialize($config);
    }

    protected function _beforeRender(Library\ControllerContext $context)
    {
        if ($this->getOption('show_action_buttons')) {
            $this->addToolbar('flat');
        }
    }

    public function getView()
    {
        if(!$this->_view instanceof Library\ViewInterface)
        {
            $view = parent::getView();

            $view->can_upload          = $this->canUpload();
            $view->can_delete_document = $this->canDelete();
            $view->can_download        = $this->canDownload();

            $this->_view = $view;
        }

        return $this->_view;
    }
}
