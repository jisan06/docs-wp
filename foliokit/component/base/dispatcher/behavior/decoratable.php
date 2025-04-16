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
 * Decoratable Dispatcher Behavior
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Dispatcher\Behavior
 */
class DispatcherBehaviorDecoratable extends Library\ControllerBehaviorAbstract
{ 
    protected $_content;

    /**
     * Decorate the response
     *
     * @param   Library\DispatcherContextInterface $context A command context object
     * @return 	void
     */
    protected function _beforeSend(Library\DispatcherContextInterface $context)
    {
        $request  = $context->getRequest();
        $response = $context->getResponse();

        if ($request->getFormat() !== 'html') {
            // Clear output from Wordpress
            $level = ob_get_level();
            while($level > 1) {
                ob_end_clean();
                $level--;
            }

            return;
        }
        else if (($request->isFormSubmit() || $request->isGet()) && !$request->isAjax()) {
            if(!$response->isDownloadable() && !$response->isRedirect())
            {
                //Render the page
                $this->_content = $result = $this->getObject('com:base.controller.document',  ['response' => $response])
                    ->layout($this->getDecorator())
                    ->render();


                if ($this->getDecorator() == 'wordpress') {
                    // Set content to null since we are going to send it in the page dispatcher ourselves
                    $response->setContent(null);
                } else {
                    $level = ob_get_level();
                    while($level > 1) {
                        ob_end_clean();
                        $level--;
                    }

                    //Set the result in the response
                    $response->setContent($result);
                }
            }
        }
    }

    /**
     * Set the content again since response (headers etc) was sent.
     *
     * Now the contents can be echoed anywhere desired
     *
     * @param Library\DispatcherContextInterface $context
     */
    protected function _afterSend(Library\DispatcherContextInterface $context)
    {
        if ($this->_content) {
            $context->getResponse()->setContent($this->_content);
        }

    }

    /**
     * Pass the response to Wordpress
     *
     * @param   Library\DispatcherContextInterface $context A command context object
     * @return 	bool
     */
    protected function _beforeTerminate(Library\DispatcherContextInterface $context)
    {
        if ($context->getRequest()->getFormat() === 'html') {
            $response = $context->getResponse();

            //Pass back to Wordpress
            if(!$response->isRedirect() && !$response->isDownloadable() && $this->getDecorator() == 'wordpress')
            {
                //Clear all headers to prevent 'headers already sent errors'. We will send the http headers.
                if(WP::is_admin())
                {
                    WP::add_filter('nocache_headers', function($headers) {
                        return [];
                    });
                }

                //Do not flush the response
                return false;
            }
        }
    }

    /**
     * Get the decorator name
     *
     * @return string
     */
    public function getDecorator()
    {
        $response = $this->getResponse();

        if($response->isError()) {
            $result = 'foliokit';
        } else {
            try {
                $result = $this->getController()->getView()->getDecorator();
            } catch (\Exception $e) {
                $result = 'foliokit';
            }
            
        }

        return $result;
    }
}