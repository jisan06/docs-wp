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
 * Editable Controller Behavior
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Controller\Behavior
 */
class ControllerBehaviorEditable extends ControllerBehaviorAbstract
{
    /**
     * The cookie path
     *
     * @var string
     */
    protected $_cookie_path;

    /**
     * Referrer lock status
     *
     * @var boolean
     */
    protected $_referrer_locked = false;

    /**
     * The cookie name
     *
     * @var string
     */
    protected $_cookie_name;

    /**
     * Constructor
     *
     * @param   ObjectConfig $config Configuration options
     */
    public function __construct(ObjectConfig $config)
    {
        parent::__construct($config);

        $this->addCommandCallback('before.read', 'setReferrer');

        // Referrer locks

        $this->addCommandCallback('after.apply', '_lockReferrer');

        // Referrer unlocks

        $this->addCommandCallback('after.save', '_unlockReferrer');
        $this->addCommandCallback('after.read', '_unlockReferrer');

        // Referrer cleanup

        $this->addCommandCallback('after.save', '_unsetReferrer');
        $this->addCommandCallback('after.cancel', '_unsetReferrer');

        // Resource locking/unlocking

        $this->addCommandCallback('after.read', '_lockResource');
        $this->addCommandCallback('after.save', '_unlockResource');
        $this->addCommandCallback('after.cancel', '_unlockResource');

        $this->_cookie_path = $config->cookie_path;
        $this->_cookie_name = $config->cookie_name;
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param  ObjectConfig $config A ObjectConfig object with configuration options
     * @return void
     */
    protected function _initialize(ObjectConfig $config)
    {
        $config->append(array(
            'cookie_name' => 'referrer',
            'cookie_path' => $this->getObject('request')->getBaseUrl()->toString(HttpUrl::PATH)
        ));

        parent::_initialize($config);
    }

    /**
     * Check if the behavior is supported
     *
     * @return  boolean  True on success, false otherwise
     */
    public function isSupported()
    {
        $mixer   = $this->getMixer();
        $request = $mixer->getRequest();

        if ($mixer instanceof ControllerModellable && $mixer->isDispatched() && $request->getFormat() == 'html') {
            return true;
        }

        return false;
    }

    /**
     * Get the referrer
     *
     * @param   ControllerContextModel $context A controller context object
     * @return  HttpUrl    A HttpUrl object
     */
    public function getReferrer(ControllerContextModel $context)
    {
        if($context->request->cookies->has($this->_cookie_name))
        {
            $referrer = $context->request->cookies->get($this->_cookie_name, 'url');
            $referrer = $this->getObject('lib:http.url', array('url' => $referrer));
        }
        else $referrer = $this->findReferrer($context);

        return $referrer;
    }

    /**
     * Set the referrer
     *
     * @param  ControllerContextModel $context A controller context object
     * @return void
     */
    public function setReferrer(ControllerContextModel $context)
    {
        if (!$this->_isReferrerLocked() && !$context->request->cookies->has($this->_cookie_name.'_locked'))
        {
            $request  = $context->request->getUrl();
            $referrer = $context->request->getReferrer();

            //Compare request url and referrer
            if (isset($referrer) && !$request->equals($referrer))
            {
                //Add the referrer cookie
                $cookie = $this->getObject('lib:http.cookie', array(
                    'name'   => $this->_cookie_name,
                    'value'  => $referrer,
                    'path'   => $this->_cookie_path
                ));

                $context->response->headers->addCookie($cookie);
            }
        }
    }

    /**
     * Find the referrer based on the context
     *
     * Method is being called when no referrer can be found in the request or when request url and referrer are
     * identical. Function should return a url that is different from the request url to avoid redirect loops.
     *
     * @param ControllerContextModel $context
     * @return HttpUrl    A HttpUrl object
     */
    public function findReferrer(ControllerContextModel $context)
    {
        $request = $this->getMixer()->getRequest();

        if ($referrer = $request->getQuery()->referrer) {
            $referrer = base64_decode($referrer);
        } else {
            $referrer = $request->getReferrer();
        }

        return $this->getObject('lib:http.url', array('url' => $referrer));
    }

    /**
     * Lock the referrer from updates
     *
     * @param  ControllerContextModel  $context A controller context object
     * @return void
     */
    protected function _lockReferrer(ControllerContextModel $context)
    {
        $cookie = $this->getObject('lib:http.cookie', array(
            'name'   => $this->_cookie_name.'_locked',
            'value'  => true,
            'path'   => $this->_cookie_path
        ));

        $context->response->headers->addCookie($cookie);

        $this->_setReferrerLocked(true);
    }

    /**
     * Unlock the referrer for updates
     *
     * @param   ControllerContextModel  $context A controller context object
     * @return void
     */
    protected function _unlockReferrer(ControllerContextModel $context)
    {
        if (!$this->_isReferrerLocked()) {
            $context->response->headers->clearCookie($this->_cookie_name.'_locked', $this->_cookie_path);
        }
    }

    /**
     * Sets referrer lock for the request
     *
     * Dispatcher::_actionPost action calls Controller::_actionRender which in turn calls Controller::_actionRead
     * before sending results. This leads to referrer being locked and unlocked in the same request.
     *
     * If referrer lock is set to true, we know that the lock happened in this request, so it is not unlocked yet.
     *
     * @param bool $locked
     * @return $this
     */
    protected function _setReferrerLocked($locked)
    {
        $this->_referrer_locked = $locked;

        return $this;
    }

    /**
     * Checks to see if the referrer has been locked during this request
     *
     * @return bool
     */
    protected function _isReferrerLocked()
    {
        return $this->_referrer_locked;
    }

    /**
     * Unset the referrer
     *
     * @param  ControllerContextModel $context A controller context object
     * @return void
     */
    protected function _unsetReferrer(ControllerContextModel $context)
    {
        if($context->result->getStatus() !== ModelEntityInterface::STATUS_FAILED) {
            $context->response->headers->clearCookie($this->_cookie_name, $this->_cookie_path);
        }
    }

    /**
     * Check if the resource is locked
     *
     * @return bool Returns TRUE if the resource is locked, FALSE otherwise.
     */
    public function isLocked()
    {
        if($this->getModel()->getState()->isUnique())
        {
            $entity = $this->getModel()->fetch();

            if($entity->isLockable() && $entity->isLocked()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the resource is lockable
     *
     * @return bool Returns TRUE if the resource is can be locked, FALSE otherwise.
     */
    public function isLockable()
    {
        $controller = $this->getMixer();

        if($controller instanceof ControllerModellable)
        {
            if($this->getModel()->getState()->isUnique())
            {
                $entity = $this->getModel()->fetch();

                if($entity->isLockable()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Lock the resource
     *
     * Only lock if the context contains a row object and if the user has an active session he can edit or delete the
     * resource. Otherwise don't lock it.
     *
     * @param   ControllerContextModel  $context A controller context object
     * @return  void
     */
    protected function _lockResource(ControllerContextModel $context)
    {
        if($this->isLockable() && $this->canEdit()) {
            $context->result->lock();
        }
    }

    /**
     * Unlock the resource
     *
     * @param  ControllerContextModel  $context A controller context object
     * @return void
     */
    protected function _unlockResource(ControllerContextModel $context)
    {
        if($this->isLockable() && $this->canEdit()) {
            $context->result->unlock();
        }
    }

    /**
     * Permission handler for save actions
     *
     * Method returns TRUE if the controller implements the ControllerModellable interface.
     *
     * @return  boolean Return TRUE if action is permitted. FALSE otherwise.
     */
    public function canSave()
    {
        if($this->getRequest()->getFormat() == 'html')
        {
            if($this->getModel()->getState()->isUnique())
            {
                if($this->canEdit() && !$this->isLocked()) {
                    return true;
                }
            }
            else
            {
                if($this->canAdd()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Permission handler for apply actions
     *
     * Method returns TRUE if the controller implements the ControllerModellable interface.
     *
     * @return  boolean Return TRUE if action is permitted. FALSE otherwise.
     */
    public function canApply()
    {
        return $this->canSave();
    }

    /**
     * Permission handler for cancel actions
     *
     * Method returns TRUE if the controller implements the ControllerModellable interface.
     *
     * @return  boolean Return TRUE if action is permitted. FALSE otherwise.
     */
    public function canCancel()
    {
        if($this->getRequest()->getFormat() == 'html') {
            return $this->canRead();
        }

        return false;
    }

    protected function _redirectBackToForm(ControllerContextModel $context, $message = '')
    {
        $request = $context->request;

        // Store fillable data on user session

        $request->getUser()->getSession()->getContainer('attribute')->set('_fillable_data', $request->getData()->toArray());

        $url = $request->getReferrer();

        $url->setQuery(['fillable' => 1], true); // Set the query as fillable

        // Lock the referrer

        $this->_lockReferrer($context);

        $context->response->setRedirect($url, $message, ControllerResponseInterface::FLASH_ERROR)->send();
    }

    /**
     * Save action
     *
     * This function wraps around the edit or add action. If the model state is unique a edit action will be
     * executed, if not unique an add action will be executed.
    *
     * This function also sets the redirect to the referrer if the action succeeds and will redirect to the
     * current url if the edit/add action fails while setting the status message.
     *
     * @param   ControllerContextModel  $context A controller context object
     * @return  ModelEntityInterface
     */
    protected function _actionSave(ControllerContextModel $context)
    {
        $action = $this->getModel()->getState()->isUnique() ? 'edit' : 'add';
        
        try
        {
            $entity = $context->getSubject()->execute($action, $context);

            $context->response->setRedirect($this->getReferrer($context));
        }
        catch(ControllerExceptionActionFailed $e)
        {
            $this->_redirectBackToForm($context, $e->getMessage());
        }

        return $entity;
    }

    /**
     * Apply action
     *
     * This function wraps around the edit or add action. If the model state is unique a edit action will be
     * executed, if not unique an add action will be executed.
     *
     * This function also sets the redirect to the current url for 'add' actions and will redirect to current
     * url if the edit/add action fails while setting the status message.
     *
     * @param    ControllerContextModel  $context A controller context object
     * @return   ModelEntityInterface
     */
    protected function _actionApply(ControllerContextModel $context)
    {
        $action = $this->getModel()->getState()->isUnique() ? 'edit' : 'add';
        
        try
        {
            $entity = $context->getSubject()->execute($action, $context);

            if($action == 'add')
            {
                $url = $this->getReferrer($context);
                if ($entity instanceof ModelEntityInterface) {
                    $url = $context->response->headers->get('Location');
                }

                $context->response->setRedirect($url);
            }
            else $context->response->setStatus(HttpResponse::NO_CONTENT);
        }
        catch(ControllerExceptionActionFailed $e)
        {
            $this->_redirectBackToForm($context, $e->getMessage());
        }

        return $entity;
    }

    /**
     * Cancel action
     *
     * This function will unlock the row(s) and set the redirect to the referrer
     *
     * @param   ControllerContextModel $context A command context object
     * @return  ModelEntityInterface
     */
    protected function _actionCancel(ControllerContextModel $context)
    {
        //Create the redirect
        $context->response->setRedirect($this->getReferrer($context));

        if ($context->result instanceof ModelEntityInterface) {
            $entity = $context->result;
        }
        else $entity = $this->getModel()->fetch();

        return $entity;
    }

    /**
     * Add a lock flash message if the resource is locked
     *
     * @param   ControllerContextModel   $context A command context object
     * @return  void
     */
    protected function _afterRead(ControllerContextModel $context)
    {
        $entity = $context->result;

        //Add the notice if the resource is locked
        if($this->canEdit() && $entity->isLockable() && $entity->isLocked())
        {
            //Prevent a re-render of the message
            if($context->request->getUrl() != $context->request->getReferrer())
            {
                if($entity->isLockable() && $entity->isLocked())
                {
                    $user    = $entity->getLocker();
                    $date    = $this->getObject('date', array('date' => $entity->locked_on));
                    $message = $this->getObject('translator')->translate(
                        'Locked by {name} {date}', array('name' => $user->getName(), 'date' => $date->humanize())
                    );

                    $context->response->addMessage($message, 'notice');
                }
            }
        }
    }

    /**
     * Prevent editing a locked resource
     *
     * If the resource is locked a Retry-After header indicating the time at which the conflicting edits are expected
     * to complete will be added. Clients should wait until at least this time before retrying the request.
     *
     * @param   ControllerContext   $context A controller context object
     * @throws  ControllerExceptionResourceLocked If the resource is locked
     * @return  void
     */
    protected function _beforeEdit(ControllerContextModel $context)
    {
        if($this->isLocked())
        {
            $context->response->headers->set('Retry-After', $context->user->getSession()->getLifetime());
            throw new ControllerExceptionResourceLocked('Resource is locked.');
        }
    }

    /**
     * Prevent deleting a locked resource
     *
     * If the resource is locked a Retry-After header indicating the time at which the conflicting edits are expected
     * to complete will be added. Clients should wait until at least this time before retrying the request.
     *
     * @param   ControllerContextModel $context A controller context object
     * @throws  ControllerExceptionResourceLocked If the resource is locked
     * @return 	void
     */
    protected function _beforeDelete(ControllerContextModel $context)
    {
        if($this->isLocked())
        {
            $context->response->headers->set('Retry-After', $context->user->getSession()->getLifetime());
            throw new ControllerExceptionResourceLocked('Resource is locked');
        }
    }
}
