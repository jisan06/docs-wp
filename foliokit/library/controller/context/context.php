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
 * Controller Context
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Controller\Context
 */
class ControllerContext extends Command implements ControllerContextInterface
{
    /**
     * Constructor.
     *
     * @param  array|\Traversable  $attributes An associative array or a Traversable object instance
     */
    public function __construct($attributes = array())
    {
        ObjectConfig::__construct($attributes);

        //Set the subject and the name
        if($attributes instanceof ControllerContextInterface)
        {
            $this->setSubject($attributes->getSubject());
            $this->setName($attributes->getName());
        }
    }

    /**
     * Get the request object
     *
     * @return ControllerRequestInterface
     */
    public function getRequest()
    {
        return ObjectConfig::get('request');
    }

    /**
     * Set the request object
     *
     * @param ControllerRequestInterface $request
     * @return ControllerContext
     */
    public function setRequest(ControllerRequestInterface $request)
    {
        return ObjectConfig::set('request', $request);
    }

    /**
     * Get the response object
     *
     * @return ControllerResponseInterface
     */
    public function getResponse()
    {
        return ObjectConfig::get('response');
    }

    /**
     * Set the response object
     *
     * @param ControllerResponseInterface $response
     * @return ControllerContext
     */
    public function setResponse(ControllerResponseInterface $response)
    {
        return ObjectConfig::set('response', $response);
    }

    /**
     * Get the user object
     *
     * @return UserInterface
     */
    public function getUser()
    {
        return ObjectConfig::get('user');
    }

    /**
     * Set the user object
     *
     * @param UserInterface $user
     * @return $this
     */
    public function setUser(UserInterface $user)
    {
        return ObjectConfig::set('user', $user);
    }
}