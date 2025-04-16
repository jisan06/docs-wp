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
 * Controller Context Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Controller\Context
 */
interface ControllerContextInterface extends CommandInterface
{
    /**
     * Get the request object
     *
     * @return ControllerRequestInterface
     */
    public function getRequest();

    /**
     * Get the response object
     *
     * @return ControllerResponseInterface
     */
    public function getResponse();

    /**
     * Get the user object
     *
     * @return UserInterface
     */
    public function getUser();
}