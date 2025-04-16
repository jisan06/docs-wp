<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ConnectHandlerPublic extends ConnectHandlerAbstract
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['property' => 'path', 'tasks' => ['mail/validate', 'embed/iframe', 'embed/oembed']]);

        parent::_initialize($config);
    }

    public function handle(Library\ControllerContextInterface $context)
    {
        $request = $context->getRequest();

        $query = $request->getQuery()->toArray();

        $path = $query['path'];

        foreach (['component', 'page_id', 'format', 'path'] as $key) {
            unset($query[$key]);
        }

        $options = [
            'method' => $request->getMethod(),
            'query'  => $query,
            'data'   => $request->getMethod() === 'GET' ? null : $request->getData()->toArray()
        ];

        $response = $this->_endpoint->connect($path, $options);
        $status   = 200;

        if ($response->status_code && isset(Library\DispatcherResponse::$status_messages[$response->status_code])) {
            $status = $response->status_code;
        }

        $context->getResponse()->setStatus($status)->setContent($response->body);
    }
}