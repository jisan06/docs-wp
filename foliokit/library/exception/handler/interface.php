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
 * Exception Handler Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Exception\Handler
 */
interface ExceptionHandlerInterface
{
    /**
     * Error Levels
     */
    const ERROR_REPORTING    = null; //Use the error_reporting() setting
    const ERROR_DEVELOPMENT  = 24575;   //E_ALL & ~E_DEPRECATED
    const ERROR_PRODUCTION   = 7;    //E_ERROR | E_WARNING | E_PARSE

    /**
     * Handler Types
     */
    const TYPE_EXCEPTION = 1;
    const TYPE_ERROR     = 2;
    const TYPE_FAILURE   = 4;
    const TYPE_ALL       = 7;

    /**
     * Enable exception handling
     *
     * @return
     */
    public function enable($type = self::TYPE_ALL);

    /**
     * Disable exception handling
     *
     * @return
     */
    public function disable($type = self::TYPE_ALL);

    /**
     * Add an exception callback
     *
     * @param  callable $callback
     * @param  bool $prepend If true, the callback will be prepended instead of appended.
     * @throws \InvalidArgumentException If the callback is not a callable
     * @return ExceptionHandlerInterface
     */
    public function addExceptionCallback(callable $callback, $prepend = false );

    /**
     * Remove an exception callback
     *
     * @param  callable $callback
     * @throws \InvalidArgumentException If the callback is not a callable
     * @return ExceptionHandlerInterface
     */
    public function removeExceptionCallback(callable $callback);

    /**
     * Get the registered exception callbacks
     *
     * @return array An array of callables
     */
    public function getExceptionCallbacks();

    /**
     * Get the handled exception stack
     *
     * @return  ObjectStack   An object stack containing the handled exceptions
     */
    public function getExceptions();

    /**
     * Set which PHP errors are handled
     *
     * @param int $level If NULL, will reset the level to the system default.
     */
    public function setErrorReporting($level);

    /**
     * Get the PHP errors that are being handled
     *
     * @return int The error level
     */
    public function getErrorReporting();

    /**
     * Handle an exception by calling all handlers that have registered to receive it.
     *
     * If an exception handler returns TRUE the exception handling will be aborted, otherwise the next handler will be
     * called, until all handlers have gotten a change to handle the exception.
     *
     * @param   Exception  $exception  The exception to be handled
     * @return  void
     */
    public function handleException(\Throwable $exception);

    /**
     * Check if an exception type is enabled
     *
     * @param $type
     * @return bool
     */
    public function isEnabled($type);
}