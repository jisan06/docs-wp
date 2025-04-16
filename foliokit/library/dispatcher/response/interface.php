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
 * Dispatcher Response Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Dispatcher\Response
 */
interface DispatcherResponseInterface extends ControllerResponseInterface
{
    /**
     * Send the response
     *
     * @param bool $terminate Whether to terminate the request by flushing it or not, defaults to TRUE
     * @return boolean  Returns true if the response has been send, otherwise FALSE
     */
    public function send($terminate = true);

    /**
     * Flush the output buffer and terminate request
     *
     * @return void
     */
    public function terminate();

    /**
     * Sets the response content using a stream
     *
     * @param FilesystemStreamInterface $stream  The stream object
     * @return DispatcherResponseInterface
     */
    public function setStream(FilesystemStreamInterface $stream);

    /**
     * Get the stream resource
     *
     * @return FilesystemStreamInterface
     */
    public function getStream();

    /**
     * Get a transport handler by identifier
     *
     * @param   mixed    $transport    An object that implements ObjectInterface, ObjectIdentifier object
     *                                 or valid identifier string
     * @param   array    $config    An optional associative array of configuration settings
     * @return DispatcherResponseInterface
     */
    public function getTransport($transport, $config = array());

    /**
     * Attach a transport handler
     *
     * @param   mixed  $transport An object that implements ObjectInterface, ObjectIdentifier object
     *                            or valid identifier string
     * @param   array $config  An optional associative array of configuration settings
     * @return DispatcherResponseInterface
     */
    public function attachTransport($transport, $config = array());
}