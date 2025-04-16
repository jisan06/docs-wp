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
 * View Context Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\View\Context
 */
interface ViewContextInterface extends CommandInterface
{
    /**
     * Get the model entity
     *
     * @return ModelEntityInterface
     */
    public function getEntity();

    /**
     * Get the view data
     *
     * @return array
     */
    public function getData();

    /**
     * Get the view parameters
     *
     * @return array
     */
    public function getParameters();
}