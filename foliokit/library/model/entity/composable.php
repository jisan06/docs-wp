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
 * Traversable Model Entity Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Model\Entity
 */
interface ModelEntityComposable extends ModelEntityInterface
{
    /**
     * Insert a new entity
     *
     * This function will either clone a entity prototype or create a new instance of the entity object for each
     * entity being inserted. By default the entity will be cloned. The entity will be stored by it's identity_key
     * if set or otherwise by it's object handle.
     *
     * @param   ModelEntityInterface|array $entity  A ModelEntityInterface object or an array of entity properties
     * @param   string  $status     The entity status
     * @return  ModelEntityComposable
     */
    public function insert($entity, $status = null);

    /**
     * Find an entity in the collection based on a needle
     *
     * This functions accepts either a know position or associative array of property/value pairs
     *
     * @param string $needle The position or the key to search for
     * @return ModelEntityInterface
     */
    public function find($needle);

    /**
     * Removes an entity from the collection
     *
     * The entity will be removed based on it's identity_key if set or otherwise by it's object handle.
     *
     * @param  ModelEntityInterface $entity
     * @return ModelEntityComposite
     * @throws \InvalidArgumentException if the object doesn't implement ModelEntityInterface
     */
    public function remove($entity);

    /**
     * Checks if the collection contains a specific entity
     *
     * @param   ModelEntityInterface $entity
     * @return  bool Returns TRUE if the object is in the set, FALSE otherwise
     */
    public function contains($entity);
}