<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;
use EasyDocLabs\WP;

class ConnectHandlerCaptcha extends ConnectHandlerAbstract
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['tasks' => ['render-captcha', 'verify-captcha']]);

        parent::_initialize($config);
    }

    protected static function _getCaptchaCache()
    {
        // TODO Re-factor to use WP cache
        $config  = \JFactory::getConfig();
        $group   = 'com_aitthemesconnect.captcha';
        $options = array(
            'caching' 		=> true,
            'defaultgroup'  => $group,
            'lifetime' 		=> 60,
            'cachebase'    => JPATH_ADMINISTRATOR.'/cache',
            'language'     => $config->get('language', 'en-GB'),
            'storage'      => $config->get('cache_handler', 'file')
        );

        return JCache::getInstance('output', $options);

    }

    protected function _taskRenderCaptcha(Library\ControllerContextInterface $context)
    {
        $parameters = $context->param;

        ksort($parameters);

        $cache_handler = static::_getCaptchaCache();
        $cache_key     = 'captcha.challenge.'.\EasyDocLabs\WP::wp_json_encode($parameters);
        $output        = $cache_handler->get($cache_key);

        if (!$output)
        {
            $result = $this->_endpoint->connect('captcha/challenge', ['method' => 'GET', 'query' => $parameters]);

            if ($result->status_code === 200) {
                $output = $result->body;

                $cache_handler->store($output, $cache_key);
            }
        }

        return $output;
    }

    protected function _taskVerifyCaptcha(Library\ControllerContextInterface $context)
    {
        $token = $context->param->token;

        if (!$token)
        {
            $data  = $context->getRequest()->getData();
            $token = $data->get('g-recaptcha-response', 'raw');
        }

        $body = (object) ['success' => false];

        try
        {
            $result = $this->_endpoint->connect('captcha/challenge', [
                'method' => 'POST',
                'data'   => ['g-recaptcha-response' => $token]
            ]);

            if ($result->status_code === 200) {
                $body = json_decode($result->body);
            }
        }
        catch (\Exception $e) {
            if (WP\DEBUG) throw $e;
        }

        return $body;
    }
}