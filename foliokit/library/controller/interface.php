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
 * Controller Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Controller
 */
interface ControllerInterface
{
    /**
     * Execute an action by triggering a method in the derived class.
     *
     * @param   string             $action  The action to execute
     * @param   ControllerContext $context A command context object
     * @throws  \BadMethodCallException
     * @return  mixed|bool      The value returned by the called method, false in error case.
     */
    public function execute($action, ControllerContext $context);

    /**
     * Gets the available actions in the controller.
     *
     * @return array Actions
     */
    public function getActions();

    /**
     * Set the request object
     *
     * @param ControllerRequestInterface $request A request object
     * @return ControllerAbstract
     */
    public function setRequest(ControllerRequestInterface $request);

    /**
     * Get the request object
     *
     * @throws \UnexpectedValueException	If the request doesn't implement the ControllerRequestInterface
     * @return ControllerRequestInterface
     */
    public function getRequest();

    /**
     * Set the response object
     *
     * @param ControllerResponseInterface $response A response object
     * @return ControllerAbstract
     */
    public function setResponse(ControllerResponseInterface $response);

    /**
     * Get the response object
     *
     * @throws	\UnexpectedValueException	If the response doesn't implement the ControllerResponseInterface
     * @return ControllerResponseInterface
     */
    public function getResponse();

    /**
     * Set the user object
     *
     * @param UserInterface $user A request object
     * @return UserInterface
     */
    public function setUser(UserInterface $user);

    /**
     * Get the user object
     *
     * @return UserInterface
     */
    public function getUser();

    /**
     * Has the controller been dispatched
     *
     * @return  boolean	Returns true if the controller has been dispatched
     */
    public function isDispatched();
}
