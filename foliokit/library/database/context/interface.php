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
 * Database Context Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Database\Context
 */
interface DatabaseContextInterface extends CommandInterface
{
    /**
     * Get the query object
     *
     * @return DatabaseQueryInterface|string
     */
    public function getQuery();

    /**
     * Get the number of affected rows
     *
     * @return integer
     */
    public function getAffected();
}