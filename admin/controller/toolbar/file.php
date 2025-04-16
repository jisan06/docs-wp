<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/fyigoto/easydocs for the canonical source repository
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Library;

class ControllerToolbarFile extends ControllerToolbarActionbar
{
    protected function _commandRefresh(Library\ControllerToolbarCommand $command)
    {
        $command->icon = 'k-icon-loop-circular';
    }

    protected function _commandCreatedocuments(Library\ControllerToolbarCommand $command)
    {
        $command->icon = 'k-icon-plus';
        $command->href = 'component=easydoc&view=upload&layout=default';
        $command->attribs->append(['class' => ['k-is-hidden']]);
    }

    protected function _afterBrowse(Library\ControllerContext $context)
    {
        $controller = $this->getController();

        $this->addNewfolder([
            'label' => 'New Folder',
            'allowed' => $controller->canAdd(),
            'icon' => 'k-icon-plus',
            'attribs' => ['class' => ['js-open-folder-modal k-button--success']]
        ]);

        $this->addCopy(['allowed' => $controller->canMove()]);
        $this->addMove(['allowed' => $controller->canMove()]);

        $this->addDelete(['allowed' => $controller->canDelete()]);
        $this->addSeparator();
        $this->addRefresh();
    }
}
