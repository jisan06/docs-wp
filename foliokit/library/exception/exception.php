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
 * Exception Interface
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Exception
 */
interface Exception
{
    /**
     * Return the exception message
     *
     * @return string
     */
    public function getMessage();

    /**
     * Return the user defined exception code
     *
     * @return integer
     */
    public function getCode();

    /**
     * Return the source filename
     *
     * @return string
     */
    public function getFile();

    /**
     * Return the source line number
     *
     * @return integer
     */
    public function getLine();

    /**
     * Return the backtrace information
     *
     * @return array
     */
    public function getTrace();

    /**
     * Return the backtrace as a string
     *
     * @return string
     */
    public function getTraceAsString();

    /**
     * Returns previous Exception
     *
     * @return \Exception Returns the previous \Exception if available or NULL otherwise.
     */
    public function getPrevious();
}