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
 * Model Context Database
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Model\Context
 */
class ModelContextDatabase extends ModelContext
{
    /**
     * Set the model query
     *
     * @param DatabaseQueryInterface $query
     * @return ModelContext
     */
    public function setQuery($query)
    {
        return ObjectConfig::set('query', $query);
    }

    /**
     * Get the model query
     *
     * @return DatabaseQueryInterface
     */
    public function getQuery()
    {
        return ObjectConfig::get('query');
    }
}