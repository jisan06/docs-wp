<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;

class ControllerBehaviorEditable extends Library\ControllerBehaviorEditable
{
    protected function _afterSave(Library\ControllerContextInterface $context)
    {
        $entity = $context->result;

        if ($entity instanceof Library\ModelEntityInterface)
        {
            $translator = $this->getObject('translator');

            $name = $entity->getIdentifier()->getName();

            if ($entity instanceof Library\ModelEntityRow || $entity->count() === 1) {
                $name = Library\StringInflector::singularize($name);
            }

            $context->getResponse()->addMessage($translator->translate(sprintf('%s saved', ucfirst($name))), Library\ControllerResponse::FLASH_SUCCESS);

            $context->response->setRedirect($this->getReferrer($context));
        }
    }
}