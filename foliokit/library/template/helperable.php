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
 * Template Helperable Interface
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Template
 */
interface TemplateHelperable
{
    /**
     * Invoke a template helper
     *
     * This function accepts a partial identifier, in the form of helper.method or schema:package.helper.method. If
     * a partial identifier is passed a full identifier will be created using the template identifier.
     *
     * If the view state have the same string keys, then the parameter value for that key will overwrite the state.
     *
     * @param    string   $identifier Name of the helper, dot separated including the helper function to call
     * @param    array    $config     An optional associative array of functions parameters to be passed to the helper
     * @return   string   Helper output
     * @throws   \BadMethodCallException If the helper function cannot be called.
     */
    public function invokeHelper($identifier, $config = array());

    /**
     * Create a template helper
     *
     * @param    mixed    $helper ObjectIdentifierInterface
     * @param    array    $config An optional associative array of configuration settings
     * @return  TemplateHelperInterface
     */
    public function createHelper($helper, $config = array());
}