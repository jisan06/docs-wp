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
 * Dispatcher Response
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
final class DispatcherResponse extends Library\DispatcherResponse
{
    public function send($terminate = true)
    {
        try
        {
            // Write session before sending the response (and thus headers)
        
            $session = $this->getUser()->getSession();
            
            if ($session instanceof UserSession) {
                $session->shutdown();
            }
        }
        catch (\Exception $e)
        {
            // Do nothing ... we've tried!
        }
        
        return parent::send($terminate);
    }
}