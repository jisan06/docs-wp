<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;
use EasyDocLabs\WP;

/**
 * Error event subscriber
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Base
 */
class EventSubscriberNotfound extends Library\EventSubscriberAbstract
{    
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(array(
            'priority' => Library\Event::PRIORITY_HIGH
        ));

        parent::_initialize($config);
    }

    public function onException(Library\EventInterface $event)
    {
        $user = $this->getObject('user');

        $exception = $event->exception;

        if (!$user->isAuthentic() && $exception instanceof \EasyDocLabs\EasyDoc\ControllerExceptionUnauthorizedCategory)
        {
            $request = $this->getObject('request');

            // Change the event context (exception) as to force a login re-direct
            if (!WP::is_admin() && $request->getFormat() == 'html' && $request->isSafe()) {
                $event->exception = new Library\HttpExceptionUnauthorized("", 0, $exception);
            }
        }
    }
}