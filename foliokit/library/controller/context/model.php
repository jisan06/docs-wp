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
 * Model Controller Context
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Controller\Context
 */
class ControllerContextModel extends ControllerContext
{
    /**
     * Set the model entity
     *
     * @param ModelEntityInterface $entity
     * @return ControllerContextModel
     */
    public function setEntity($entity)
    {
        return ObjectConfig::set('entity', $entity);
    }

    /**
     * Get the model entity
     *
     * @return ModelEntityInterface
     */
    public function getEntity()
    {
        return ObjectConfig::get('entity');
    }
}