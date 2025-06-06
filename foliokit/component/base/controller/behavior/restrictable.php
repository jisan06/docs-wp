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
use EasyDocLabs\WP;

/**
 * Restrictable Controller Behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Base
 */
class ControllerBehaviorRestrictable extends Library\ControllerBehaviorAbstract implements Library\ObjectMultiton
{
    protected $_component_map = ['easydoc' => 'EasyDocs'];

    protected $_grace_period;

    protected $_actions;

    protected $_restricted;

    public function __construct(Library\ObjectConfig $config)
    {   
        parent::__construct($config);

        $this->_actions = $config->actions->toArray();

        $this->_grace_period = $config->grace_period;

        if ($this->isRestricted()) {
            $this->_setRestrictable($config->tables);
        }
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['actions' => [], 'grace_period' => 7, 'tables' => []]);

        parent::_initialize($config);
    }

    protected function _setRestrictable($tables)
    {
        $identifier = $this->getIdentifier();

        $manager = $this->getObject('manager');

        $behavior = 'com:base.database.behavior.restrictable';

        foreach ($tables as $table)
        {
            if (!$table instanceof Library\ObjectIdentifierInterface)
            {
                $table = (string) $table;

                if (strpos($table, '.') === false) {
                    $table = sprintf('com://%s/%s.database.table.%s', $identifier->getDomain(), $identifier->getPackage(), Library\StringInflector::pluralize($table));
                }
            }

            if ($manager->isRegistered($table)) {
                $this->getObject($table)->addBehavior($behavior, ['actions' => $this->_actions]);
            } else {
                $manager->getIdentifier($table)->getConfig()->append(['behaviors' => [$behavior => ['actions' => $this->_actions]]]);
            }
        }
    }

    protected function _beforeRender(Library\ControllerContextInterface $context)
    {
        $result = true;

        if (!$this->_isLocal() && WP::is_admin() && $context->getRequest()->getFormat() == 'html')
        {
            $license = $this->_getLicense();

            $translator = $this->getObject('translator');

            if ($license->hasError())
            {
                $context->_message = $translator->translate('license error', ['error' => $translator->translate($license->getError()), 'url' => 'https://dashboard.system.ait-themes.club']);
                
                $this->_redirect($context);

                $result = false;
            } 
            elseif ($this->isRestricted(true))
            {
                if ($this->_isWihtinGracePeriod($license))
                {
                    $message = $this->getObject('translator')->translate('license recent expiry', ['component' => $this->_getComponent()]);

                    $context->getResponse()->addMessage($message, Library\ControllerResponseInterface::FLASH_WARNING);
                }
                else 
                {
                    $context->_message = $translator->translate('license expiry', ['component' => $this->_getComponent()]);

                    $this->_redirect($context);
                    
                    $result = false;
                } 
            }
            elseif ($subscription = $license->getSubscription($this->_getComponent(true)))
            {
                if (isset($subscription['cancelled']) && $subscription['cancelled'])
                {
                    $remaining = ($subscription['end'] - time())/604800;

                    if ($remaining <= 4) // A month before
                    {
                        $message = $this->getObject('translator')->translate('subscription cancelled', ['component' => $this->_getComponent()]);

                        $context->getResponse()->addMessage($message, Library\ControllerResponseInterface::FLASH_WARNING);
                    }                        
                }
            }
        }

        return $result;
    }

    protected function _isWihtinGracePeriod($license)
    {
        $result = false;

        if ($subscription = $license->getSubscription($this->_getComponent(true), false))
        {
            $past = (time() - $subscription['end'])/86400;

            if ($past <= $this->_grace_period) {
                $result = true;
            }
        }

        return $result;
    }

    protected function _getComponent($raw = false)
    {
        $identifier = $this->getMixer()->getIdentifier();

        $component = $identifier->getPackage();

        if (!$raw) {
            if (isset($this->_component_map[$component])) $component = $this->_component_map[$component];
        }

        return $component;
    }

    protected function _redirect(Library\ControllerContextInterface $context)
    {
        $request  = $context->getRequest();
        $response = $context->getResponse();

        $config = $this->getConfig();

        if (!$config->redirect_url)
        {
            $referrer = $request->getReferrer();

            $url = $referrer ?: $request->getSiteUrl();
        }
        else $url = $config->redirect_url;

        $type = $context->_message_type ?? Library\ControllerResponseInterface::FLASH_ERROR;

        if (!$context->_message) throw \RuntimeException('Restrictable re-direct call is missing a message');

        $response->setRedirect($url, $context->_message, $type);
    }

    public function getRestrictedActions()
    {
        return $this->_actions;
    }

    public function isRestrictedAction($action)
    {
        if (strpos($action, 'can') === 0) {
            $action = Library\StringInflector::underscore(str_replace('can', '', $action));
        }

        return in_array($action, $this->_actions);
    }

    protected function _getLicense()
    {
        return $this->getObject('license');
    }

    public function isRestricted($strict = false)
    {
        if (!isset($this->_restricted))
        {
            $result = true;

            try
            {
                $license = $this->_getLicense();
                
                $result = !$license->hasFeature($this->_getComponent(true));
    
                if ($result && !$strict && $this->_grace_period) {
                    $result = !$this->_isWihtinGracePeriod($license);
                }
            }
            catch(\Exception $e)
            {
                // Exceptions are handled as expired subs
    
                $result = true;
            }
    
            if ($this->_isLocal()) $result = false;

            $this->_restricted = $result;
        }
        else $result = $this->_restricted;

        return $result;
    }

    protected function _isLocal()
    {
        static $local_hosts = array('localhost', '127.0.0.1', '::1');

        $url  = $this->getObject('request')->getUrl();
        $host = $url->host;

        if (in_array($host, $local_hosts)) {
            return true;
        }

        // Returns true if host is an IP address
        if (ip2long($host))
        {
            return (filter_var($host, FILTER_VALIDATE_IP,
                    FILTER_FLAG_IPV4 |
                    FILTER_FLAG_IPV6 |
                    FILTER_FLAG_NO_PRIV_RANGE |
                    FILTER_FLAG_NO_RES_RANGE) === false);
        }
        else
        {
            // If no TLD is present, it's definitely local
            if (strpos($host, '.') === false) {
                return true;
            }

            return preg_match('/(?:\.)(local|localhost|test|example|invalid|dev|box|intern|internal)$/', $host) === 1;
        }
    }
}