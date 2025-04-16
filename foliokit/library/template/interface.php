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
  * Template Interface
  *
  * @author  Johan Janssens <https://github.com/johanjanssens>
  * @package EasyDocLabs\Library\Template
  */
interface TemplateInterface
{
    /**
     * Render a template
     *
     * @param   string  $source  The template url or content
     * @param   array   $data    An associative array of data to be extracted in local template scope
     * @return  string  The rendered template source
     */
    public function render($source, array $data = array());

    /**
     * Get a template data property
     *
     * @param   string  $property The property name.
     * @param   mixed   $default  Default value to return.
     * @return  string  The property value.
     */
    public function get($property, $default = null);

    /**
     * Get the template data
     *
     * @return  array   The template data
     */
    public function getData();

    /**
     * Register a template function
     *
     * @param string  $name      The function name
     * @param string  $function  The callable
     * @return TemplateInterface
     */
    public function registerFunction($name, $function);

    /**
     * Unregister a template function
     *
     * @param string    $name   The function name
     * @return TemplateInterface
     */
    public function unregisterFunction($name);
}
