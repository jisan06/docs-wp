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

/**
 * Error event subscriber
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class EventSubscriberException extends Library\EventSubscriberException
{
    /**
     * Before fail error handler
     *
     * To be used for custom error handling instead of default
     *
     * @param Library\EventInterface $event The event object
     */
    public function onException(Library\EventInterface $event)
    {
        $this->_renderError($event);
    }

    /**
     * Error renderer
     *
     * @param Library\EventInterface $event The event object
     * @return boolean|null
     */
    protected function _renderError(Library\EventInterface $event)
    {
        $request   = $this->getObject('request');

        //Get the exception object
        $exception = $event->exception;

        //Make sure the output buffers are cleared
        $level = ob_get_level();
        while($level > 0) {
            ob_end_clean();
            $level--;
        }

        //If the error code does not correspond to a status message, use 500
        $code = $exception->getCode();
        if(!isset(Library\HttpResponse::$status_messages[$code])) {
            $code = '500';
        }

        if($request->getFormat() === 'html' || $request->getFormat() === 'json')
        {
            //Render the exception if debug mode is enabled or if we are returning json
            $dispatcher = $this->getObject('dispatcher');
            
            //Set status code (before rendering the error)
            $dispatcher->getResponse()->setStatus($code);

            $content = $this->getObject('com:base.controller.error',  ['request'  => $request])
                ->layout('default')
                ->render($exception);

            //Set error in the response
            $dispatcher->getResponse()->setContent($content);
            $dispatcher->send();
        } else {
            // Other formats like format=raw or format=rss. Show the error and stop
            $message = sprintf('%s: \'%s\' thrown in %s on line %s.<br>%s',
                get_class($exception),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                $exception->getTraceAsString()
            );
            echo $message;

            die;
        }
    }
}