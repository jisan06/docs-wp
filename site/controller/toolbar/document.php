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

class ControllerToolbarDocument extends EasyDoc\ControllerToolbarActionbar
{
	protected function _afterRead(Library\ControllerContext $context)
    {
        $layout = $this->getObject('request')->query->get('layout', 'cmd');

        if ($layout === 'form')
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
    
            // TODO: This delete button works alright but we have yet to deal with the redirect after POST
    
            /*if ($controller->isEditable() && $controller->canDelete() && $context->result->id) {
                $this->addCommand('delete', [
                    'attribs' => [
                        'class' => ['k-button--link']
                    ]
                ]);
            }*/
        }
    }

    public function getCommands()
    {
        $layout = $this->getObject('request')->query->get('layout', 'cmd');

        // Batch delete button is only available in gallery and table

        if ($this->getController()->canDelete() && in_array($layout, ['table', 'gallery']))
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

        return parent::getCommands();
    }
}
