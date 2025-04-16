<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2011 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Library;

class ControllerToolbarCategory extends ControllerToolbarActionbar
{
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

        $allowed = true;

        if (isset($context->result) && $context->result->isLockable() && $context->result->isLocked()) {
            $allowed = false;
        }

        if ($controller->isEditable() && $controller->canDelete() && $context->result->id)
        {
            $this->addCommand('delete', [
                'allowed' => $allowed,
                'attribs' => [
                    'class' => ['k-button--link']
                ]
            ]);
        }
    }
}
