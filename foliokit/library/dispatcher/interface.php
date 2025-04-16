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
 * Dispatcher Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Dispatcher
 */
interface DispatcherInterface extends ControllerInterface
{
    /**
     * Method to get a controller object
     *
     * @return  ControllerAbstract
     */
    public function getController();

    /**
     * Method to set a controller object attached to the dispatcher
     *
     * @param   mixed   $controller An object that implements ControllerInterface, ObjectIdentifier object
     *                              or valid identifier string
     * @param  array  $config  An optional associative array of configuration options
     * @return	DispatcherInterface
     */
    public function setController($controller, $config = array());

    /**
     * Method to get a controller action to be executed
     *
     * @return	string
     */
    public function getControllerAction();

    /**
     * Method to set the controller action to be executed
     *
     * @return	DispatcherInterface
     */
    public function setControllerAction($action);
}
