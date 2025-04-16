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
 * View Templatable Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\View
 */
interface ViewTemplatable
{
    /**
     * Get the layout
     *
     * @return string The layout name
     */
    public function getLayout();

    /**
     * Qualify the layout
     *
     * Convert a relative layout URL into an absolute layout URL
     *
     * @param string $layout The view layout name
     * @param string $type   The filesystem locator type
     * @return string   The fully qualified template url
     */
    public function qualifyLayout($layout, $type = 'com');

    /**
     * Get the template object attached to the view
     *
     *  @throws	\UnexpectedValueException	If the template doesn't implement the TemplateInterface
     * @return  TemplateInterface
     */
    public function getTemplate();
}