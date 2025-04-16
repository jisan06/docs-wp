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
 * Model Context Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Model\Context
 */
interface ModelContextInterface extends CommandInterface
{
    /**
     * Get the model state
     *
     * @return ModelState
     */
    public function getState();

    /**
     * Get the identity key
     *
     * @return mixed
     */
    public function getIdentityKey();
}