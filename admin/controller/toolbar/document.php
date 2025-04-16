<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/fyigoto/easydocs for the canonical source repository
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Library;

class ControllerToolbarDocument extends ControllerToolbarActionbar
{
    protected function _commandUpload(Library\ControllerToolbarCommand $command)
    {
        $category = $this->getObject('request')->query->category;

        $command->icon = 'k-icon-data-transfer-upload';
        $command->href = 'javascript:;';
        $command->append([
            'data'    => [
                'k-modal' => [
                    'items'     => [
                        'src'  => (string)$this->getController()->getView()->getRoute('component=easydoc&view=upload&layout=default&category_id=' . $category),
                        'type' => 'iframe'
                    ],
                    'modal'     => true,
                    'mainClass' => 'koowa_dialog_modal'
                ]
            ],
            'attribs' => [
                'class' => array('btn btn-default'),
            ]
        ]);

        parent::_commandDialog($command);
    }

    protected function _afterBrowse(Library\ControllerContext $context)
    {
        if ($this->getController()->getView()->getLayout() === 'attachments') {
            $this->addCommand('selected_documents', [
                'href'    => '#',
                'attribs' => ['class' => ['k-button--success']],
                'icon'    => 'k-icon-check',
                'label'   => 'Insert selected documents'
            ]);
        } else parent::_afterBrowse($context);
    }

    protected function _afterRead(Library\ControllerContext $context)
    {
        parent::_afterRead($context);

        $controller = $this->getController();
        $referrer   = $controller->getReferrer($context);

        // Need this hack to get the eventual redirect URL from the editable behavior

        $controller->setReferrer($context);
        $context->request->cookies->get('referrer', 'url');

        foreach ($context->getResponse()->getHeaders()->getCookies() as $cookie) {
            if ($cookie->name === 'referrer') {
                $referrer = $cookie->value;
            }
        }

        $this->removeCommand('cancel');

        $this->addCommand('discard', [
            'data' => [
                'referrer' => $referrer
            ]
        ]);

        if ($controller->isEditable() && $controller->canDelete() && $context->result->id) {
            $this->addCommand('delete', [
                'attribs' => [
                    'class' => ['k-button--link']
                ]
            ]);
        }
    }
}
