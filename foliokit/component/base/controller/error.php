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

/**
 * Exception Controller
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class ControllerError extends Library\ControllerView
{
    /**
     * Initializes the default configuration for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   Library\ObjectConfig $config Configuration options
     * @return void
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'formats' => ['json'],
        ]);

        parent::_initialize($config);
    }

    /**
     * Render an error
     *
     * @throws \InvalidArgumentException If the action parameter is not an instance of KException
     * @param  Library\ControllerContextInterface $context	A controller context object
     * @return string
     */
    protected function _actionRender(Library\ControllerContext $context)
    {
        //Check an exception was passed
        if(!isset($context->param) && !$context->param instanceof Library\Exception)
        {
            throw new \InvalidArgumentException(
                "Action parameter 'exception' [EasyDocLabs\Library\Exception] is required"
            );
        }

        //Set the exception data in the view
        $exception = $context->param;

        //If the error code does not correspond to a status message, use 500
        $code = $exception->getCode();
        if(!isset(Library\HttpResponse::$status_messages[$code])) {
            $code = '500';
        }

        $message = Library\HttpResponse::$status_messages[$code];

        //Get the exception back trace
        $traces = $this->getBackTrace($exception);

        //Traverse up the trace stack to find the actual function that was not found
        if(isset($traces[0]['function']) && $traces[0]['function'] == '__call')
        {
            foreach($traces as $trace)
            {
                if($trace['function'] != '__call')
                {
                    $file     = isset($trace['file']) ? $trace['file']  : '';
                    $line     = isset($trace['line']) ? $trace['line']  : '';
                    $function = isset($trace['function']) ? $trace['function'] : '';
                    $class    = isset($trace['class']) ? $trace['class'] : '';
                    $args     = isset($trace['args'])  ? $trace['args']  : '';
                    $info     = isset($trace['info'])  ? $trace['info']  : '';
                    $type     = isset($trace['type'])  ? $trace['type']  : '';
                    $message = "Call to undefined method : ".$class.$type.$function;
                    break;
                }
            }
        }
        else
        {
            $message  = $exception->getMessage();
            $file	  = $exception->getFile();
            $line     = $exception->getLine();
            $function = isset($traces[0]['function']) ? $traces[0]['function'] : '';
            $class    = isset($traces[0]['class']) ? $traces[0]['class']    : '';
            $args     = isset($traces[0]['args'])  ? $traces[0]['args']     : '';
            $info     = isset($traces[0]['info'])  ? $traces[0]['info']     : '';
        }

        //Create the exception message
        if(!\Foliokit::isDebug()) {
            $traces = [];
        }

        $this->getView()->exception = get_class($exception);
        $this->getView()->code      = $code;
        $this->getView()->message   = $message;
        $this->getView()->file      = $file;
        $this->getView()->line      = $line;
        $this->getView()->function  = $function;
        $this->getView()->class     = $class;
        $this->getView()->args      = $args;
        $this->getView()->info      = $info;
        $this->getView()->trace     = $traces;
        $this->getView()->level     = $exception instanceof Library\ExceptionError ? $exception->getSeverityMessage() : false;

        //Render the exception
        $result = parent::_actionRender($context);

        return $result;
    }

    public function getBackTrace(\Throwable $exception)
    {
        $traces = [];

        if($exception instanceof Library\ExceptionError)
        {
            $traces = $exception->getTrace();

            //Remove the first trace containing the call to KExceptionHandler
            unset($traces[0]);
        }
        else $traces = $exception->getTrace();

        //Remove the keys from the trace, we don't need those.
        $traces = array_values($traces);

        return $traces;
    }
}