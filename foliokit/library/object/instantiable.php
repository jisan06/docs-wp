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
 * Object Instantiable Interface
 *
 * The interface signals the ObjectManager to delegate object instantiation.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Object
 * @see     ObjectManager::getObject()
 */
interface ObjectInstantiable
{
    /**
     * Instantiate the object
     *
     * @param   ObjectConfigInterface $config      Configuration options
     * @param   ObjectManagerInterface $manager    A ObjectManagerInterface object
     * @return  ObjectInterface
     */
    public static function getInstance(ObjectConfigInterface $config, ObjectManagerInterface $manager);
}
