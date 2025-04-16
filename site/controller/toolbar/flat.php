<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/fyigoto/easydocs for the canonical source repository
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\EasyDoc;
use EasyDocLabs\Library;

class ControllerToolbarFlat extends EasyDoc\ControllerToolbarActionbar
{
    public function getCommands()
    {
        $controller = $this->getController();

        $query = $controller->getRequest()->getQuery();

        $layout = $query->get('layout', 'cmd');

        // Batch delete button is only available in gallery and table
        if ($controller->canDelete() && in_array($layout, ['table', 'gallery']))
        {
            $data = [
                '_method' => 'delete'
            ];

            $this->addCommand('delete', [
                'attribs' => [
                    'class' => ['btn btn-danger'],
                    'data-params' => htmlentities(\EasyDocLabs\WP::wp_json_encode($data))
                ]
            ]);
        }

        $slug   = $query->slug || $query->uuid;
        $filter = $query->filter;

        $is_compressible = $controller->getOption('allow_multi_download');
        $show            = true;//!empty($slug) || !empty($filter); // TODO Ask Ercan what's this about

        if ($controller->canDownload() && $layout !== 'list' && $show && $is_compressible)
        {
            $this->addCommand('download', [
                'label'   => 'Download selected',
                'icon'    => 'k-icon-cloud-download',
                'href'    => '#',
                'attribs' => [
                    'class'    => ['btn k-js-multi-download k-is-disabled'],
                    'data-url' => $this->getObject('router')->generate('easydoc:', [
                        'endpoint' => '~documents',
                        'view'     => 'download'
                    ])->toString()
                ]
            ]);
        }

        return parent::getCommands();
    }

    protected function _commandUpload(Library\ControllerToolbarCommand $command)
    {
		$controller = $this->getController();

        $command->icon = 'k-icon-data-transfer-upload';
        $command->href = 'javascript:;';

        $command->append([
            'data' => [
                'k-modal' => [
                    'items' => [
                        'src'  => (string) $controller->getView()->getRoute('component=easydoc&view=upload&layout=default&options=' . $controller->getQueryOptions()),
                        'type' => 'iframe'
                    ],
                    'modal' => true,
                    'mainClass' => 'koowa_dialog_modal'
                ]
            ],
            'attribs' => [
                'class' => array('btn btn-default'),
            ]
        ]);

        parent::_commandDialog($command);
    }

    protected function _commandDocument(Library\ControllerToolbarCommand $command)
    {
		$controller = $this->getController();

        $category      = $controller->getModel()->fetch();
        $command->href = $controller->getView()->getRoute('view=document&layout=form&slug=&category_slug=' .
                                                                     ($category->slug ? $category->slug : '') .
                                                                     '&options=' . $controller->getQueryOptions());

        $translator = $this->getObject('translator');

        $command->label = $translator->translate('Add document');
        $command->icon = 'k-icon-plus';

        $command->attribs->merge([
            'class' => ['btn btn-success']
        ]);
    }

    protected function _afterBrowse(Library\ControllerContext $context)
    {
        $controller = $this->getController();

        if ($controller->canUpload($controller->getModel()->getState()->isUnique() ? $context->getEntity()->id : null))
        {
            $this->addDocument();
            $this->addUpload();
        }
    }
}
