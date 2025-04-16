<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ConnectHandlerTest extends ConnectHandlerAbstract
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['tasks' => ['scanner-test']]);

        parent::_initialize($config);
    }

    public function requiresAuthorization()
    {
        return false;
    }

    protected function _taskScannerTest(Library\ControllerContextInterface $context)
    {
        $request   = $context->getRequest();
        $query     = $request->getQuery();
        $operation = $query->get('test', 'cmd');

        $is_callback = ($request->getMethod() === 'POST' && $operation === 'callback');
        $is_get      = ($request->getMethod() === 'GET' && $operation === 'get');

        if ($is_callback || $is_get) {
            $data   = $request->getData();
            $body   = 'ok';
            $status = 200;

            if (!$this->_endpoint->validateToken($query->token)) {
                $status = 500;
                $body   = 'token-error';
            }
            elseif ($is_callback && !$data->has('post-data-check')) {
                $status = 500;
                $body   = 'post-data-error';
            }

            return $context->getResponse()->setStatus($status)->setContent($body, 'application/json');
        }

        $callback_url = $this->_endpoint->getRoute(['task' => 'scanner-test', 'test' => 'callback', 'token' => $this->_endpoint->generateToken()]);
        $download_url = $this->_endpoint->getRoute(['task' => 'scanner-test', 'test' => 'get', 'token' => $this->_endpoint->generateToken()]);

        $data = array(
            'download_url' => (string)$download_url,
            'callback_url' => (string)$callback_url,
            'filename'     => 'dummy.txt',
            'user_data'    => array(
                'uuid' => 'dummy'
            )
        );

        $response = $this->_endpoint->connect('scanner/test', [
            'data' => $data, 'exception' => false
        ]);

        $body = @json_decode($response->body, true);

        if (!$response->status_code || json_last_error() !== JSON_ERROR_NONE || !is_array($body) || !isset($body['body'])) {
            $response->body = \EasyDocLabs\WP::wp_json_encode([
                'error' => 'endpoint-error',
                'statusCode' => 0,
                'body' => json_last_error() === JSON_ERROR_NONE ? $body : ($response->body ?: null),
                'isLocal' => $this->_endpoint->isLocal(),
                'isSupported' => $this->_endpoint->isSupported(),
                'version' => $this->_endpoint->getVersion(),
            ]);
            $response->status_code = 500;

        } elseif (is_array($body) && isset($body['body'])) {
            $body['isLocal'] = $this->_endpoint->isLocal();
            $body['isSupported'] = $this->_endpoint->isSupported();
            $body['version'] = $this->_endpoint->getVersion();

            $response->body = \EasyDocLabs\WP::wp_json_encode($body);
        }

        return $context->getResponse()->setStatus($response->status_code)
            ->setContent($response->body, 'application/json')
            ->send();
    }
}