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
 * Fragment Dispatcher Permission
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Dispatcher\Permission
 */
class DispatcherPermissionFragment extends DispatcherPermissionAbstract
{
    /**
     * Permission handler for dispatch actions
     *
     * @return  boolean  Return TRUE if action is permitted. FALSE otherwise.
     */
    public function canDispatch()
    {
        return true;
    }

    /**
     * Permission handler for include actions
     *
     * @return  boolean  Return TRUE if action is permitted. FALSE otherwise.
     */
    public function canInclude()
    {
        return true;
    }
}
