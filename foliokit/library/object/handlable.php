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
 * Object Handlable Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Object
 */
interface ObjectHandlable
{
    /**
     * Get the object handle
     *
     * This function returns an unique identifier for the object. This id can be used as a hash key for storing objects
     * or for identifying an object
     *
     * Override this function to implement implement dynamic commands. If you don't want the command to be enqueued in
     * a chain return NULL instead of a valid handle.
     *
     * @return string A string that is unique, or NULL
     */
    public function getHandle();
}
