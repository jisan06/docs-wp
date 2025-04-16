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
 * Http Request
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Http\Request
 */
interface HttpRequestInterface extends HttpMessageInterface
{
    /**
     * Return the accept header
     *
     * Parses the accept header and returns an array (type => quality) of the accepted types, ordered by quality.
     *
     * @link : https://tools.ietf.org/html/rfc7231#page-38
     *
     * @param array   $defaults  The default values
     * @return array
     */
    public function getAccept(array $defaults = NULL);

    /**
     * Get the cache control
     *
     * @link https://tools.ietf.org/html/rfc7234#section-5.2.1
     * @return array
     */
    public function getCacheControl();

    /**
     * Set the method for this request
     *
     * @param  string $method
     * @throws \InvalidArgumentException
     * @return HttpRequestInterface
     */
    public function setMethod($method);

    /**
     * Return the method for this request
     *
     * @return string
     */
    public function getMethod();

    /**
     * Set the url for this request
     *
     * @param string|HttpUrl   $url
     * @throws \InvalidArgumentException If the url is not an instance of HttpUrl or a string
     * @return HttpRequest
     */
    public function setUrl($url);

    /**
     * Return the url for this request
     *
     * @return HttpUrl
     */
    public function getUrl();

    /**
     * Is this an OPTIONS method request?
     *
     * @return bool
     */
    public function isOptions();

    /**
     * Is this a GET method request?
     *
     * @return bool
     */
    public function isGet();

    /**
     * Is this a HEAD method request?
     *
     * @return bool
     */
    public function isHead();

    /**
     * Is this a POST method request?
     *
     * @return bool
     */
    public function isPost();

    /**
     * Is this a PUT method request?
     *
     * @return bool
     */
    public function isPut();

    /**
     * Is this a DELETE method request?
     *
     * @return bool
     */
    public function isDelete();

    /**
     * Is this a TRACE method request?
     *
     * @return bool
     */
    public function isTrace();

    /**
     * Is this a CONNECT method request?
     *
     * @return bool
     */
    public function isConnect();

    /*
     * Is this a PATCH method request?
     *
     * @return bool
     */
    public function isPatch();

    /**
     * Is the request an ajax request
     *
     * @return boolean
     */
    public function isAjax();

    /**
     * Is the request a submitted HTTP form?
     *
     * @return boolean
     */
    public function isFormSubmit();

    /**
     * Is the request cacheable
     *
     * @return boolean
     */
    public function isCacheable();
}