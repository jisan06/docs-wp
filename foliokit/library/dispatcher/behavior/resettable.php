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
 * Resettable Dispatcher Behavior - Post, Redirect, Get
 *
 * When a browser sends a POST request (e.g. after submitting a form), the browser will try to protect them from sending
 * the POST again, breaking the back button, causing browser warnings and pop-ups, and sometimes re-posting the form.
 *
 * Instead, when receiving a none AJAX POST request reset the browser by redirecting it through a GET request.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Dispatcher\Behavior
 */
class DispatcherBehaviorResettable extends DispatcherBehaviorAbstract
{
    /**
     * Check if the behavior is supported
     *
     * @return  boolean  True on success, false otherwise
     */
    public function isSupported()
    {
        return $this->getMixer()->getRequest()->isFormSubmit();
    }

    /**
     * Force a GET after POST using the referrer
     *
     * Redirect if the controller has a returned a 2xx status code.
     *
     * @param   DispatcherContext $context The active command context
     * @return  void
     */
    protected function _beforeSend(DispatcherContext $context)
    {
        $response = $context->response;
        $request  = $context->request;

        if($response->isSuccess() && $referrer = $request->getReferrer()) {
            $response->setRedirect($referrer);
        }
    }
}
