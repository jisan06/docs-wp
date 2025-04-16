<?php
/**
 * FolioKit Tags
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Tags;

use EasyDocLabs\Library;

/**
 * Tag Controller Toolbar
 *
 * @author  Tom Janssens <http://github.com/tomjanssens>
 * @package Koowa\Component\Tags
 */
class ControllerToolbarTag extends Library\ControllerToolbarActionbar
{
    /**
     * New tag toolbar command
     *
     * @param Library\ControllerToolbarCommand $command
     */
    protected function _commandNew(Library\ControllerToolbarCommand $command)
    {
        $component = $this->getController()->getIdentifier()->package;
        $view      = Library\StringInflector::singularize($this->getIdentifier()->name);

        $command->href = 'component='.$component.'&view='.$view;
    }
}