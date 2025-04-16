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

require_once __DIR__.'/../resources/install/helper.php';

class License extends Library\ObjectAbstract implements Library\ObjectSingleton
{
    const LICENSE_ENDPOINT = 'https://system.ait-themes.club';
    const PUBLIC_KEY_ENDPOINT = '';

    const API_KEY_OPTION = 'easydocs_api_key';
    const LICENSE_OPTION = 'easydocs_license';
    const SITE_KEY_OPTION = 'easydocs_site_key';
    const PUBLIC_KEY_OPTION = 'easydocs_public_key';

    /** @var CryptoToken */
    protected $_token;

    /** @var null|string */
    protected $_error;

    protected $_subscriptions_map = [
        'easydoc' => 7,
        'logman' => 8,
        'fileman' => 9,
        'textman' => 11,
        'leadman' => 31
    ];

    public function hasError()
    {
        return (bool) $this->_error;
    }

    public function getError()
    {
        return $this->_error;
    }

    protected function _validateToken($token)
    {
        if (!$token->verify($this->getPublicKey())) {
            throw new LicenseException('Cannot verify license');
        }

        if ($token->isExpired()) {
            throw new LicenseException('License is not valid anymore');
        }

        $site = $token->getClaim('site');

        if (!$this->isLocal() && $site['key'] !== "ffffffff-ffff-ffff-ffff-ffffffffffff" && $site['key'] !== $this->getSiteKey()) {
            throw new LicenseException('License is for a different site');
        }
    }

    public function load()
    {
        try {
            $license = $this->getLicense();

            if (!$license) {
                throw new LicenseException('Cannot find license');
            }

            if (!$this->getPublicKey()) {
                throw new LicenseException('Cannot find public key');
            }

            if (!$this->getSiteKey()) {
                throw new LicenseException('Cannot find site key');
            }

            $token = $this->decode($license);

            $this->_validateToken($token);

            $this->_token = $token;

            return true;
        }
        catch (LicenseException $e) {
            $this->_error = $e->getMessage();

            return false;
        }

    }

    public function getToken()
    {
        if (!$this->_token && !$this->hasError()) {
            $this->load();
        }

        return $this->_token;
    }

    public function isValid()
    {
        if (!$this->_token && !$this->hasError()) {
            $this->load();
        }

        if ($this->hasError() || time() > $this->getExpiry()) {
            return false;
        }

        return true;
    }

    public function hasFeature($feature)
    {
        if (!$this->_token && !$this->hasError()) {
            $this->load();
        }

        if ($this->hasError()) {
            return false;
        }

        switch($feature)
        {
            case 'connect':
                $connect = $this->getToken()->getClaim('connect');
                $result = $connect && $connect['enabled'] === true;
                break;
            case 'easydoc':
            case 'fileman':
            case 'logman':
            case 'leadman':
            case 'textman':
                $result = is_array($this->getSubscription($feature));
                break;
            default:
                $result = $this->isValid();
                break;
        }

        return $result;
    }

    public function getSubscription($component, $active = true)
    {
        $result = false;

        $subscriptions = [1, 5, 6, 13, 4]; // Higher level subscriptions including individual components

        $subscriptions[] = $this->_subscriptions_map[$component];

        foreach ($this->getSubscriptions() as $subscription)
        {
            if (in_array($subscription['id'], $subscriptions))
            {
                $match = null;
                $now   = time();

                if ($active)
                {
                    if ($subscription['end'] > $now) {
                        $match = $subscription;
                    }
                }
                elseif ($subscription['end'] <= $now) $match = $subscription;

                if ($match && is_array($result))
                {
                    // Keep the most recent

                    if ($match['end'] > $result['end']) $result = $match;
                }
                else $result = $match;
            }
        }

        return $result;
    }

    protected function _isAgency()
    {
        return (bool) array_intersect([6, 13], array_column($this->getSubscriptions(), 'id'));
    }

    protected function _isBusiness()
    {
        return in_array(5, array_column($this->getSubscriptions(), 'id'));
    }

    protected function _isBusinessOrHigher()
    {
        return (bool) array_intersect([5, 6, 13, 4], array_column($this->getSubscriptions(), 'id'));
    }

    public function getSubscriptions()
    {
        $token = $this->getToken();

        return !$this->hasError() ? ($token->getClaim('subscriptions') ?: []) : [];
    }

    public function getExpiry()
    {
        $end = 0;

        foreach ($this->getSubscriptions() as $subscription) {
            if ($subscription['end'] > $end) {
                $end = $subscription['end'];
            }
        }

        return $end;
    }

    public function getConnectKeys()
    {
        return $this->isValid() && $this->hasFeature('connect') ? $this->getToken()->getClaim('connect') : [];
    }

    public function getCustomer()
    {
        return $this->getToken()->getClaim('sub');
    }

    public function getLicense()
    {
        return WP::get_option(static::LICENSE_OPTION);
    }

    public function getPublicKey()
    {
        return WP::get_option(static::PUBLIC_KEY_OPTION);
    }

    public function getApiKey()
    {
        return WP::get_option(static::API_KEY_OPTION);
    }

    public function getSiteKey()
    {
        return WP::get_option(static::SITE_KEY_OPTION);
    }

    public function setApiKey($api_key)
    {
        WP::update_option(static::API_KEY_OPTION, trim($api_key));
    }

    public function setLicense($license)
    {
        WP::update_option(static::LICENSE_OPTION, trim($license));
    }

    public function setPublicKey($public_key)
    {
        WP::update_option(static::PUBLIC_KEY_OPTION, trim($public_key));
    }

    public function setSiteKey($site_key)
    {
        WP::update_option(static::SITE_KEY_OPTION, trim($site_key));
    }

    public function setApiKeyFromFile($file)
    {
        if (is_file($file)) {
            $api_key = file_get_contents($file);

            if ($api_key) {
                $this->setApiKey($api_key);
            }
        }
    }

    public function setLicenseKeyFromFile($file)
    {
        if (is_file($file)) {
            $license_key = file_get_contents($file);

            if ($license_key) {
                $this->setLicense($license_key);
            }
        }
    }

    public function setPublicKeyFromFile($file)
    {
        if (is_file($file)) {
            $public_key = file_get_contents($file);

            if ($public_key) {
                $this->setPublicKey($public_key);
            }
        }
    }

    public function savePublicKey()
    {
        $public_key = $this->_sendRequest(static::PUBLIC_KEY_ENDPOINT);

        if ($public_key->status_code === 200) {
            $this->setPublicKey($public_key->body);

            return true;
        }

        return false;
    }

    public function saveLicenseToken($params = [])
    {
        $license = isset($params['license']) ? $params['license'] : '';
        $route = '/core/account/verification';
        $parse_url = wp_parse_url(site_url());
        $license_domain = $parse_url['host'];
        $token_request = wp_remote_post(
            static::LICENSE_ENDPOINT . $route,
            [
                'body' => json_encode([
                    'key' => $license,
                    'domain' => $license_domain,
                    'package' => '',
                    'products' => ['easy-docs-ait'],
                    'active' => true
                ]),
            ]
        );
        $response = $token_request['response'];
        if (isset( $response['code'] ) && $response['code'] === 200 ) {
//            $body = json_decode(wp_remote_retrieve_body($token_request), true);
//            if (!empty($body) && !empty($body->license)) {
//                $this->setLicense($body->license);
//            }
            $this->setLicense($license);
        }else {
            $this->setLicense('');
        }
    }

    public function saveSiteKey()
    {
        if (!$this->getSiteKey()) {
            $site_key = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );

            $this->setSiteKey($site_key);
        }
    }

    public function getSiteData()
    {
        global $wp_version, $wpdb;

        return [
            "key" => $this->getSiteKey(),
            "is_local" => $this->isLocal(),
            "is_valid" => $this->isValid(),
            "public_key" => $this->getPublicKey(),
            "api_key" => $this->getApiKey(),
            "url" => get_home_url(),
            "wordpress" => $wp_version,
            "php" => phpversion(),
            "mysql" => $wpdb->db_version(),
            "extensions" => [
                "easydoc" => WP::get_option('easydocs_easydoc_version')
            ]
        ];
    }


    public function onInstall()
    {
        try {
            $base = WP\PLUGIN_DIR . '/easy-docs-ait/base/resources/install/';
            $keys = ['.api.key', '.public.key', '.license.key'];

            $this->setPublicKeyFromFile($base . '.public.key');
            $this->setApiKeyFromFile($base . '.api.key');
            $this->setLicenseKeyFromFile($base . '.license.key');
    
            // Remove the keys from filesystem so they are not visible to the world
            foreach ($keys as $key) {
                if (is_file($base . $key)) {
                    unlink($base . $key);
                }
            }

            $this->refresh();
        }
        catch (\Exception $e) {}
    }

    public function refresh($params = [])
    {
//        $this->saveSiteKey();
//        $this->savePublicKey();
        $this->saveLicenseToken($params);
    }

    /**
     * @param $string
     * @return CryptoToken
     */
    public function decode($string)
    {
        try {
            /** @var CryptoToken $token */
            $token = $this->getObject('com:base.crypto.token');
            $token->fromString($string);

            return $token;
        } catch (\Exception $e) {
            throw new LicenseException('Cannot create token from license', 0, $e);
        }
    }

    protected function _sendRequest($url, $options = array())
    {
        if (isset($options['query'])) {
            if (is_array($options['query'])) {
                $options['query'] = http_build_query($options['query'], '', '&');
            }

            $url .= '?'.$options['query'];
        }

        $method = isset($options['method']) ? strtoupper($options['method']) : (isset($options['data']) ? 'POST' : 'GET');

        $response = wp_remote_request($url, [
            'method' => $method,
            'body' => isset($options['data']) ? json_encode($options['data']) : null,
            'headers' => (isset($options['headers']) ? $options['headers'] : []),
            'sslverify' => false,
        ]);

        if (is_wp_error($response)) {
            throw new \RuntimeException('Request Error: '.$response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        
        if (isset($status_code) && ($status_code < 200 || $status_code >= 300)
            && (!isset($options['exception']) || $options['exception'] !== false)) {
            throw new \UnexpectedValueException('Problem in the request. Request returned '. $status_code, $status_code);
        }

        $result = new \stdClass();
        $result->status_code = $status_code;
        $result->body        = wp_remote_retrieve_body($response);

        return $result;
    }

    /**
     * Returns true if the site is running on localhost
     *
     * @return string
     */
    public function isLocal()
    {
        static $local_hosts = ['localhost', '127.0.0.1', '::1'];

        $url  = $this->getObject('request')->getUrl();
        $host = $url->host;

        if (in_array($host, $local_hosts)) {
            return true;
        }

        // Returns true if host is an IP address
        if (ip2long($host)) {
            return (filter_var($host, FILTER_VALIDATE_IP,
                    FILTER_FLAG_IPV4 |
                    FILTER_FLAG_IPV6 |
                    FILTER_FLAG_NO_PRIV_RANGE |
                    FILTER_FLAG_NO_RES_RANGE) === false);
        }
        else {
            // If no TLD is present, it's definitely local
            if (strpos($host, '.') === false) {
                return true;
            }

            return preg_match('/(?:\.)(local|localhost|test|example|invalid|dev|box|intern|internal)$/', $host) === 1;
        }
    }
}