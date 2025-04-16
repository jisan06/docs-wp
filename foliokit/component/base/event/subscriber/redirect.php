<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/nooku/nooku-framework-wordpress for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Library;
use EasyDocLabs\WP;

/**
 * Redirect event subscriber
 *
 * Handle guest login re-directs
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Base
 */
class EventSubscriberRedirect extends Library\EventSubscriberAbstract
{
    public function onException(Library\EventInterface $event)
    {
        $exception = $event->exception;

        // Handle guest login re-directs
        if($exception instanceof Library\HttpExceptionUnauthorized)
        {
            $request   = $this->getObject('request');
            $response  = $this->getObject('response');

            if ($request->getFormat() == 'html' && $request->isSafe())
            {
                if(!WP::is_admin())
                {
                    if (!$this->getObject('user')->isAuthentic())
                    {
                        $url = $request->getUrl();
                        $url->setQuery('easydoc_login_redirect=1', true);

                        $url = WP::wp_login_url(base64_encode($url->toString()));

                        $response->setRedirect($url, '', Library\ControllerResponseInterface::FLASH_ERROR);

                        $response->send();

                        $event->stopPropagation();
                    }
                }
            }
        }
    }

}