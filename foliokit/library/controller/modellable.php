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
 * Controller Modellable Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Controller
 */
interface ControllerModellable
{
    /**
     * Get the controller model
     *
     * @throws  \UnexpectedValueException    If the model doesn't implement the ModelInterface
     * @return	ModelInterface
     */
    public function getModel();
}