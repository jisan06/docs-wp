<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

/**
 * Connect endpoint interface
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\EasyDoc
 */
interface ConnectEndpoint extends Library\ControllerInterface
{
    /**
     * Sends an HTTP request and returns the response
     *
     * @param string $path    Request path including the query string
     * @param array  $options Request options. Valid keys include method, data, query, and callback
     * @return mixed The response
     */
    public function connect($path, array $options = []);

    /**
     * Adds a handler to the endpoint for handling incoming requests
     *
     * @param mixed $handler The handler to add
     * @throws \RuntimeException If an invalid handler is provided
     * @return mixed
     */
    public function addHandler($handler);

    /**
     * Handlers getters
     *
     * @return mixed A collection of handlers
     */
    public function getHandlers();
}