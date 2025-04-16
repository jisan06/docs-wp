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
 * Native Session Handler
 *
 * It uses the default registered PHP session handler, whatever that might be
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\User\Session\Handler
 * @link    http://www.php.net/manual/en/function.session-set-save-handler.php
 */
class UserSessionHandlerNative extends UserSessionHandlerAbstract
{
    /**
     * Do nothing since we are going to depend on the current PHP session handler
     */
    public function register()
    {
        static::$_registered = $this;
    }
}