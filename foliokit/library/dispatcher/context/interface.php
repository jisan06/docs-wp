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
 * Dispatcher Context Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Dispatcher\Context
 */
interface DispatcherContextInterface extends ControllerContextInterface
{
    /**
     * The request has been successfully authenticated
     *
     * @return Boolean
     */
    public function isAuthentic();

    /**
     * Sets the request as authenticated
     *
     * @return $this
     */
    public function setAuthentic();
}