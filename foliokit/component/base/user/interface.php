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
 * User Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
interface UserInterface extends Library\UserInterface
{
    /**
     * Returns the username of the user
     *
     * @return string The username
     */
    public function getUsername();

    /**
     * Method to check object authorisation
     *
     * @param   string  $action  The name of the action to check for permission.
     * @param   string  $object  The name of the object on which to perform the action.
     * @return  boolean  True if authorised
     */
    public function authorise($action, $object = null);
}