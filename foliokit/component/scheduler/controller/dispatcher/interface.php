<?php
/**
 * FolioKit Scheduler
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */
namespace EasyDocLabs\Component\Scheduler;

use EasyDocLabs\Library;

/**
 * Dispatcher interface
 *
 * @author Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Scheduler
 */
interface ControllerDispatcherInterface extends Library\ControllerInterface
{
    /**
     * Get the controller model
     *
     * @throws  \UnexpectedValueException    If the model doesn't implement the ModelInterface
     * @return	Library\ModelInterface
     */
    public function getModel();

    /**
     * Set the controller model
     *
     * @param   mixed   $model An object that implements ObjectInterface, ObjectIdentifier object
     *                         or valid identifier string
     * @return	Library\ControllerInterface
     */
    public function setModel($model);
}