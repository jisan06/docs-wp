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
 * Controller Viewable Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Controller
 */
interface ControllerViewable
{
    /**
     * Get the controller view
     *
     * @throws  \UnexpectedValueException    If the view doesn't implement the ViewInterface
     * @return  ViewInterface
     */
    public function getView();

    /**
     * Get the supported formats
     *
     * @return array
     */
    public function getFormats();
}