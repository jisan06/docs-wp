<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Library;

/**
 * Action Controller Toolbar
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class ControllerToolbarActionbar extends Library\ControllerToolbarActionbar
{
    /**
     * Initializes the config for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   Library\ObjectConfig $config Configuration options
     * @return  void
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'title' => ucfirst($this->getName()),
            'icon'  => $this->getName(),
        ]);

        parent::_initialize($config);
    }

    /**
     * Add default action commands and set the action bar title
     * .
     * @param Library\ControllerContext $context A command context object
     */
    protected function _afterRead(Library\ControllerContext $context)
    {
        $controller = $this->getController();
        $translator = $this->getObject('translator');
        $name       = $translator->translate(strtolower($context->subject->getIdentifier()->name));

        if($controller->getModel()->getState()->isUnique()) {
            $title = $translator->translate('Edit {item_type}', ['item_type' => $name]);
        } else {
            $title = $translator->translate('Create new {item_type}', ['item_type' => $name]);
        }

        $this->getCommand('title')->title = $title;

        parent::_afterRead($context);

        $this->removeCommand('apply');
    }

    /**
     * Add a title command
     *
     * @param   string $title   The title
     * @param   string $icon    The icon
     * @return  Library\ControllerToolbarAbstract
     */
    public function addTitle($title, $icon = '')
    {
        $this->_commands['title'] = new Library\ControllerToolbarCommand('title', [
            'title' => $title,
            'icon'  => $icon
        ]);
        return $this;
    }

    /**
     * Publish Toolbar Command
     *
     * @param   Library\ControllerToolbarCommand $command  A KControllerToolbarCommand object
     * @return  void
     */
    protected function _commandPublish(Library\ControllerToolbarCommand $command)
    {
        $this->_commandEnable($command);
    }

    /**
     * Unpublish Toolbar Command
     *
     * @param   Library\ControllerToolbarCommand $command  A KControllerToolbarCommand object
     * @return  void
     */
    protected function _commandUnpublish(Library\ControllerToolbarCommand $command)
    {
        $this->_commandDisable($command);
    }
}
