<?php
/**
 * Foliokit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     MPL v2.0 <https://www.mozilla.org/en-US/MPL/2.0>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Library;

/**
 * Dispatcher request transport for decoding data
 *
 * Decodes the request payload for various content types and pushes the results into the data object
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package EasyDocLabs\Library\Dispatcher\Request\Transport
 */
class DispatcherRequestTransportData extends DispatcherRequestTransportAbstract
{
    /**
     * Set the request data
     *
     * @param DispatcherRequestInterface $request
     */
    public function receive(DispatcherRequestInterface $request)
    {
        if($request->getContentType() == 'application/x-www-form-urlencoded')
        {
            if (in_array($request->getMethod(), array('PUT', 'DELETE', 'PATCH')))
            {
                parse_str($request->getContent(), $data);
                $request->getData()->add($data);
            }
        }

        if(in_array($request->getContentType(), array('application/json', 'application/x-json', 'application/vnd.api+json')))
        {
            if(in_array($request->getMethod(), array('POST', 'PUT', 'DELETE', 'PATCH')))
            {
                $data = array();

                if ($content = $request->getContent()) {
                    $data = json_decode($content, true);
                }

                if ($data) {
                    $request->getData()->add($data);
                }

                // Transform the JSON API request payloads
                if($request->getContentType() == 'application/vnd.api+json')
                {
                    if (is_array($request->data->data))
                    {
                        $data = $request->data->data;

                        if (isset($data['attributes']) && is_array($data['attributes'])) {
                            $request->data->add($data['attributes']);
                        }
                    }
                }
            }
        }
    }
}