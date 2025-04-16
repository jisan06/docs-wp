<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;
use EasyDocLabs\WP;

/**
 * Connect controller
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\EasyDoc
 */
class ControllerConnect extends Base\ControllerModel implements Library\ObjectMultiton, ConnectEndpoint
{
    const URL = "https://api.system.ait-themes.club/";

    const VERSION = '2.5.0';

    protected $_api_key = '';

    protected $_secret_key = '';

    protected $_router_endpoint;

    /** @var ConnectHandlerInterface[] */
    protected $_handlers;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        // Load settings from configuration file for local debugging
        $connect = sprintf('%s/%s', WP\CONTENT_DIR, 'easydoclabs/connect.php');

        if (file_exists($connect))
        {
            $connect = include $connect;

            unset($config['api_key']);
            unset($config['secret_key']);

            $config->append($connect);
        }

        $this->_api_key         = $config->api_key;
        $this->_secret_key      = $config->secret_key;
        $this->_router_endpoint = $config->router_endpoint;

        $this->_handlers = $this->getObject('lib:object.queue');

        foreach ($config->handlers as $handler) {
            $this->addHandler($handler);
        }
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $keys = $this->getObject('license')->getConnectKeys();

        $config->append([
            'api_key'         => isset($keys['token']) ? $keys['token'] : null,
            'secret_key'      => isset($keys['secret']) ? $keys['secret'] : null,
            'model'           => 'lib:model.empty',
            'request'         => $this->getObject('request'),
            'router_endpoint' => '~documents',
            'handlers'        => ['easydoc', 'public', 'test']
        ]);

        parent::_initialize($config);
    }

    /**
     * Adds a handler to the endpoint for handling incoming requests
     *
     * @param mixed $handler The handler to add
     * @throws \RuntimeException If an invalid handler is provided
     * @return mixed
     */
    public function addHandler($handler)
    {
        if(!($handler instanceof ConnectHandlerInterface))
        {
            if(is_string($handler) && strpos($handler, '.') === false )
            {
                $identifier         = $this->getIdentifier()->toArray();
                $identifier['path'] = ['connect', 'handler'];
                $identifier['name'] = $handler;

                $identifier = $this->getIdentifier($identifier);
            }
            else $identifier = $this->getIdentifier($handler);

            $handler = $this->getObject($identifier, ['connect' => $this]);
        }

        if ($handler instanceof ConnectHandlerInterface) {
            $this->_handlers->enqueue($handler, $handler->getPriority());
        } else {
            throw new \RuntimeException(sprintf('Handler must be of type ConnectHandlerInterface, %s was given', get_class($handler)));
        }

        return $this;
    }

    /**
     * Handlers getters
     *
     * @return ConnectHandlerInterface[] A collection of handlers
     */
    public function getHandlers()
    {
        return $this->_handlers;
    }

    /**
     * Handles a request by forwarding it to the corresponding connect handler if one is found
     *
     * @param Library\ControllerContext $context
     * @throws Base\DispatcherResponse::BAD_REQUEST If the task is not supported by any signed handler
     */
    protected function _actionTask(Library\ControllerContext $context)
    {
        foreach ($this->getHandlers() as $handler)
        {
            if ($handler->canHandle($context))
            {
                if (!$handler->requiresAuthorization() || $this->_validateToken($context)) {
                    $handler->handle($context);
                    $context->taskHandled = true;
                    break;
                }
            }
        }

        $response = $context->getResponse();

        if ($context->taskHandled !== true) {
            $context->getResponse()->setStatus(Base\DispatcherResponse::BAD_REQUEST)->setContent(\EasyDocLabs\WP::wp_json_encode(['error' => 'Unsupported connect task']))->send();
        }

        if ($this->isLocal(true) && $this->getConfig()->external_url) {
            $response->getHeaders()->set('Access-Control-Allow-Origin', '*'); // Avoid cross origin restrictions on self requests using an external URL
        }

        $response->send();
    }

    protected function _actionRender(Library\ControllerContext $context)
    {
        $this->task($context);
    }

    protected function _actionAdd(Library\ControllerContext $context)
    {
        $this->task($context);
    }

    protected function _actionEdit(Library\ControllerContext $context)
    {
        throw new Library\HttpExceptionBadRequest('Edit action is not supported');
    }

    protected function _actionDelete(Library\ControllerContext $context)
    {
        throw new Library\HttpExceptionBadRequest('Delete action is not supported');
    }

    protected function _fetchEntity(Library\ControllerContext $context)
    {
        // Do nothing
    }

    /**
     * Connect API key getter
     *
     * @return string|null The key if set
     */
    protected function _getApiKey()
    {
        return $this->_api_key;
    }

    /**
     * Connect secret key getter
     *
     * @return string|null The key if set
     */
    protected function _getSecretKey()
    {
        return $this->_secret_key;
    }

    /**
     * Route getter
     *
     * @param string $query The query
     * @return Library\HttpUrlInterface The route
     */
    public function getRoute($query = '')
    {
        $router = $this->getObject('com:base.dispatcher.router.site', ['request' => $this->getObject('request')]);

        $route = $router->generate($this->getIdentifier()->getPackage() . ':', [
            'endpoint' => $this->_router_endpoint,
            'view'     => 'connect'
        ]);

        return $route->setQuery($query, true)->setHost($this->_getSiteUrl()->getHost());
    }

    protected function _getSiteUrl()
    {
        if ($this->getConfig()->external_url) {
            $url = $this->getObject('lib:http.url', ['url' => $this->getConfig()->external_url]); // Use external URL as set in config file
        } else {
            $url = clone $this->getObject('request')->getSiteUrl();
        }

        return $url;
    }

    /**
     * Connect site getter
     *
     * @return string The site URL as known by the connect service
     */
    public function getSite()
    {
        if ($this->getConfig()->site) {
            $url = $this->getConfig()->site; // Use config override
        } else {
            $url = $this->_getSiteUrl()->toString();
        }

        return $url;
    }

    /**
     * Sends an HTTP request and returns the response
     *
     * @param string $path    Request path including the query string
     * @param array  $options Request options. Valid keys include method, data, query, and callback
     * @return \stdClass The response
     */
    public function connect($path, array $options = [])
    {
        $curl = curl_init();

        $url = self::URL.trim($path, '/').'/';

        if (isset($options['query'])) {
            if (is_array($options['query'])) {
                $options['query'] = http_build_query($options['query'], '', '&');
            }

            $url .= '?'.$options['query'];
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CUSTOMREQUEST => isset($options['method']) ? strtoupper($options['method']) : "POST",
            CURLOPT_POSTFIELDS => isset($options['data']) ? \EasyDocLabs\WP::wp_json_encode($options['data']) : null,
            CURLOPT_HTTPHEADER => [
                "Content-type: application/json",
                "Referer: ".$this->getSite(),
                "Authorization: Bearer ".$this->generateToken()
            ],
        ]);

        if (isset($options['callback']) && is_callable($options['callback']))
        {
            $callback = $options['callback'];
            $callback($curl, $path, $options);
        }

        $response = curl_exec($curl);

        if (curl_errno($curl) && (!isset($options['exception']) || $options['exception'] !== false)) {
            throw new \RuntimeException('Curl Error: '.curl_error($curl));
        }

        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (isset($status_code) && ($status_code < 200 || $status_code >= 300)
            && (!isset($options['exception']) || $options['exception'] !== false)) {
            throw new \UnexpectedValueException('Problem in the request. Request returned '. $status_code, $status_code);
        }

        curl_close($curl);

        $result              = new \stdClass();
        $result->status_code = $status_code;
        $result->body        = $response;

        return $result;
    }

    /**
     * Validates a signed JWT token
     *
     * @param Library\ControllerContextInterface $context The context
     */
    protected function _validateToken(Library\ControllerContextInterface $context)
    {
        if (!$this->validateToken($context->getRequest()->getQuery()->token)) {
            $context->getResponse()
                    ->setContent(\EasyDocLabs\WP::wp_json_encode(['error' => 'token-error']), 'application/json')
                    ->setStatus(Base\DispatcherResponse::INTERNAL_SERVER_ERROR)
                    ->send();

            return false;
        }

        return true;
    }

    public function validateToken($token)
    {
        try {
            $jwt = $this->getObject('http.token');

            $jwt->fromString($token);

            return $jwt->verify($this->_secret_key) && !$jwt->isExpired();
        } catch (\Exception $e) {
            return false;
        }

    }

    /**
     * Returns a signed JWT token for the current API key in plugin settings
     *
     * @return string The token
     */
    public function generateToken()
    {
        $token = $this->getObject('http.token');

        $date = new \DateTime('now', new \DateTimeZone('UTC'));

        return $token->setSubject($this->_getApiKey())
                     ->setExpireTime($date->modify('+1 hours'))
                     ->sign($this->_getSecretKey());
    }

    /**
     * Returns if the site is running on localhost
     *
     * @param bool $strict Whether a strict check should be performed or not
     * @return string True if local, false otherwise
     */
    public function isLocal($strict = false)
    {
        $result = !isset($this->getConfig()->external_url);

        if ($strict || $result)
        {
            $local_hosts = array('localhost', '127.0.0.1', '::1');

            $host = $this->getRequest()->getUrl()->host;

            if (!in_array($host, $local_hosts))
            {
                // Returns true if host is an IP address
                if (ip2long($host))
                {
                    $result = (filter_var($host, FILTER_VALIDATE_IP,
                            FILTER_FLAG_IPV4 |
                            FILTER_FLAG_IPV6 |
                            FILTER_FLAG_NO_PRIV_RANGE |
                            FILTER_FLAG_NO_RES_RANGE) === false);
                }
                else
                {
                    // If no TLD is present, it's definitely local
                    if (strpos($host, '.') === false) {
                        $result = true;
                    } else {
                        $result = preg_match('/(?:\.)(local|localhost|test|example|invalid|dev|box|intern|internal)$/', $host) === 1;
                    }
                }
            }
            else $result = true;
        }

        return $result;
    }

    /**
     * Checks is connect is supported on the current install
     *
     * @return bool True if supported, false otherwise
     */
    public function isSupported()
    {
        return $this->getObject('license')->hasFeature('connect');
    }

    /**
     * API version getter
     *
     * @return string The API version number
     */
    public function getVersion()
    {
        return self::VERSION;
    }
}