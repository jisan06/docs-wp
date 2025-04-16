<?php
/**
 * Foliokit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Activities;

use EasyDocLabs\Library;

/**
 * Activity Controller Toolbar.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Activities
 */
class ControllerToolbarActivity extends Library\ControllerToolbarActionbar
{
    protected function _afterBrowse(Library\ControllerContextInterface $context)
    {
        if ($this->getController()->canPurge()) {
            $this->addPurge();
        }

        return parent::_afterBrowse($context);
    }

    protected function _commandPurge(Library\ControllerToolbarCommandInterface $command)
    {
        $command->append([
            'attribs' => [
                'data-action'     => 'purge',
                'data-novalidate' => 'novalidate',
                'data-prompt'     => $this->getObject('translator')
                                          ->translate('Deleted items will be lost forever. Would you like to continue?')
            ]
        ]);
    }
}