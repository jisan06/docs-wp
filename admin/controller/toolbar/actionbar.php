<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\EasyDoc;
use EasyDocLabs\Library;

class ControllerToolbarActionbar extends EasyDoc\ControllerToolbarActionbar
{
    protected function _afterBrowse(Library\ControllerContext $context)
    {
        parent::_afterBrowse($context);

        $controller = $this->getController();
        $identifier = $context->subject->getIdentifier();
        $request    = $context->subject->getRequest();

        $base_path  = $request->getBasePath();
        $new_link   = $base_path . '/admin.php?component='.$identifier->package.'&view='.$identifier->name;
        if ($identifier->name === 'document' && $request->query->category) {
            $new_link .= '&category=' . $request->query->category;
        }

        $this->removeCommand('new')->removeCommand('delete');
        $this->addCommand('new', [
            'href'    => $new_link,
            'allowed' => $controller->canAdd()
        ]);

        if ($identifier->name === 'document') {
            $this->addUpload([
                'allowed' => $this->getObject('com:easydoc.controller.category')
                                  ->canUpload($controller->getModel()->getState()->category ?? null)
            ]);
        }

        $this->addCommand('delete');

        if ($identifier->name === 'document' || $identifier->name === 'category') {
            $this->addSeparator();
            $this->addPublish(['allowed' => $controller->canEdit()]);
            $this->addUnpublish(['allowed' => $controller->canEdit()]);
        }

        if ($identifier->name === 'document')
        {
            $this->addMove(array('allowed' => true));//$controller->canEdit()));
            $this->addCopy(array('allowed' => true));//$controller->canAdd()));

            if ($this->getObject('com:easydoc.model.configs')->fetch()->canConfigure()) {
                $this->addBatch(array('allowed' => true));//$controller->canEdit()));
            }
        }
    }

    protected function _commandMove(Library\ControllerToolbarCommand $command)
    {
        $command->attribs['href']            = '#';
        $command->icon                       = 'k-icon-move';
        $command->attribs['data-permission'] = 'edit';
    }

    protected function _commandCopy(Library\ControllerToolbarCommand $command)
    {
        $command->icon                       = 'k-icon-layers';
        $command->label                      = 'Duplicate';
        $command->attribs['data-permission'] = 'copy';
    }

    protected function _commandBatch(Library\ControllerToolbarCommand $command)
    {
        $command->attribs['href'] = '#';
        $command->icon = 'k-icon-pencil';
    }
}
