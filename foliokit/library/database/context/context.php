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
 * Database Context
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Database\Context
 */
class DatabaseContext extends Command implements DatabaseContextInterface
{
    /**
     * Constructor.
     *
     * @param  array|\Traversable  $attributes An associative array or a Traversable object instance
     */
    public function __construct($attributes = array())
    {
        ObjectConfig::__construct($attributes);

        //Set the subject and the name
        if($attributes instanceof DatabaseContextInterface)
        {
            $this->setSubject($attributes->getSubject());
            $this->setName($attributes->getName());
        }
    }

    /**
     * Get the query object
     *
     * @return DatabaseQueryInterface|string
     */
    public function getQuery()
    {
        return ObjectConfig::get('query');
    }

    /**
     * Set the query object
     *
     * @param DatabaseQueryInterface|string $query
     * @return DatabaseContext
     */
    public function setQuery($query)
    {
        return ObjectConfig::set('query', $query);
    }

    /**
     * Get the number of affected rows
     *
     * @return integer
     */
    public function getAffected()
    {
        return ObjectConfig::get('affected');
    }

    /**
     * Get the number of affected rows
     *
     * @param integer $affected
     * @return DatabaseContext
     */
    public function setAffected($affected)
    {
        return ObjectConfig::set('affected', $affected);
    }
}