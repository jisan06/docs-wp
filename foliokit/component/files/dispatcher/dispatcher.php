<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Files;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

/**
 * Dispatcher
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class Dispatcher extends Base\Dispatcher
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        //Render an exception before sending the response
        $this->getObject('event.publisher')->addListener('onException', [$this, 'renderError']);
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'limit' => [
                'max' => 2000 // Used in tree view
            ]
        ]);

        parent::_initialize($config);
    }

    /**
     * Plupload do not pass the error to our application if the status code is not 200
     *
     * @param Library\Event $event
     * @return bool
     * @throws \Exception
     */
    public function renderError($event)
    {
        if ($this->getRequest()->getFormat() == 'json')
        {
            $exception = $event->exception;

    		$response = new \stdClass;
    		$response->status = false;
    		$response->error  = $exception->getMessage();
    		$response->code   = $exception->getCode();

    		$status_code = $this->getRequest()->query->plupload ? 200 : ($exception->getCode() && $exception->getCode() <= 505 ? $exception->getCode() : 500);

            $this->getResponse()
                ->setStatus($status_code)
                ->setContent(json_encode($response), 'application/json')
                ->send();

            return false;
    	}
    }

    // FIXME: this is here because forwarded dispatchers still render results
    protected function _actionSend(Library\DispatcherContext $context)
    {
        if (!$context->getRequest()->isGet() || $context->getResponse()->getContentType() !== 'text/html') {
            return parent::_actionSend($context);
        }
    }
}
