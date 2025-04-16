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
 * Exception Handler
 *
 * Setup error handler for Wordpress context.
 *
 * 1. xdebug enabled
 *
 * If xdebug is enabled assume we are in local development mode
 *    - error types   : TYPE_ALL which will trigger an exception for : exceptions, errors and failures
 *    - error levels  : ERROR_DEVELOPMENT (E_ALL | E_STRICT | ~E_DEPRECATED)
 *
 * 2. Wordpress debug
 *
 * If debug is enabled assume we are in none local debug mode
 *    - error types   : TYPE_ALL which will trigger an exception for : exceptions, errors and failures
 *    - error levels  : E_ERROR and E_PARSE
 *
 * 3. Wordpress default
 *
 * Do not try to trigger errors or exceptions automatically. To trigger an exception the implementing code
 * should call {@link handleException()}
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
final class ExceptionHandler extends Library\ExceptionHandler
{
    /**
     * Initializes the default configuration for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param  Library\ObjectConfig $config An optional ObjectConfig object with configuration options.
     * @return void
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        if(\Foliokit::isDebug()) {
            $config->append([
                'exception_type'  => self::TYPE_ALL,
                'error_reporting' => self::ERROR_DEVELOPMENT
            ]);
        }
        else {
            $config->append(['exception_type' => false]);
        }

        parent::_initialize($config);
    }
}