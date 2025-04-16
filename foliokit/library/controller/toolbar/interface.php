<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Library;

/**
 * Controller Toolbar Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Controller\Toolbar
 */
interface ControllerToolbarInterface extends \IteratorAggregate, \Countable
{
    /**
     * Get the toolbar's name
     *
     * @return string
     */
    public function getName();

    /**
     * Get the toolbar's title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Add a command by name
     *
     * @param   string	$name    The command name
     * @param	array   $config  An optional associative array of configuration settings
     * @return  ControllerToolbarCommandInterface  The command object that was added
     */
    public function addCommand($name, $config = array());

    /**
     * Get a command by name
     *
     * @param string $name  The command name
     * @param array $config  An optional associative array of configuration settings
     * @return ControllerToolbarCommandInterface|boolean A toolbar command if found, false otherwise.
     */
    public function getCommand($name, $config = array()) ;

    /**
     * Check if a command exists
     *
     * @param string $name  The command name
     * @return boolean True if the command exists, false otherwise.
     */
    public function hasCommand($name);
}
