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
 * Message event subscriber
 *
 * Allows for graceful error handling by rendering a proper message.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Base
 */
class EventSubscriberMessage extends Library\EventSubscriberAbstract
{
    protected static $_handlers = [];

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        foreach($config->handlers as $handler) {
            static::addHandler($handler);
        }
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'priority' => Library\Event::PRIORITY_LOW,
            'handlers'  => [
                function($exception) {
                    if ($exception instanceof Library\ControllerExceptionResourceNotFound) {
                        return 'The resource you are trying to access was not found';
                    } else if ($exception instanceof Library\ControllerExceptionRequestForbidden) {
                        return 'You are not authorized to access this resource.';
                    }
                },
                function($exception) {
                    if ($exception instanceof Library\DatabaseException || $exception instanceof \mysqli_sql_exception) {
                        return 'A database error has occurred. Please enable debug mode for more information.';
                    }
                }
            ]
        ]);

        parent::_initialize($config);
    }

    public static function addHandler(callable $handler) {
        static::$_handlers[] = $handler;
    }

    /**
     * Before fail error handler
     *
     * @param Library\EventInterface $event The event object
     */
    public function onException(Library\EventInterface $event)
    {
        if (!\Foliokit::isDebug())
        {
            $exception = $event->exception;
            $handlers  = static::$_handlers;

            // Add a last resort message handler
            $handlers[] = function($exception) {
                return $exception->getMessage() ?: get_class($exception);
            };

            foreach ($handlers as $handler) 
            {
                $message = $handler($exception);
                
                if ($message !== null) 
                {
                    $request = $this->getObject('request');

                    if ($request->getFormat() == 'html') 
                    {
                        $response = $this->getObject('response');

                        $response->addMessage($this->getObject('translator')->translate($message), 'error');
                            
                        if ($request->isSafe())
                        {
                            $view = $this->getObject('com:base.view.html', [
                                'template_filters' => ['message', 'link', 'meta', 'asset', 'style'],
                                'layout'           => 'com:base/document/wordpress.html'
                            ]);
            
                            $view->setContent($view->getTemplate()->render('<?= helper(\'ui.load\', [\'domain\' => \'' . ( WP::is_admin() ? 'admin' : 'site') . '\']) ?>', [], 'php'));

                            $response->setContent($view->render());        

                            $event->stopPropagation();
                        } 
                        else 
                        {
                            // For POST requests directly render the error

                            $code = $exception->getCode();
                            if(!isset(Library\HttpResponse::$status_messages[$code])) {
                                $code = '500';
                            }
            
                            $dispatcher = $this->getObject('dispatcher');
                            $dispatcher->getResponse()->setStatus($code);
                            
                            $dispatcher->send();

                            $event->stopPropagation();
                        }
                    } 
                    else if ($request->getFormat() === 'json') 
                    {
                        // Rewrite error message but let the parent render the JSON error
                        $event->exception = new \RuntimeException($message, $exception->getCode(), $exception);
                    } else {
                        // Other formats like format=raw or format=rss. Show the error and stop
                        echo $message; 
                        
                        $event->stopPropagation();
                    }

                    return;
                }
            }
        }
    }
}