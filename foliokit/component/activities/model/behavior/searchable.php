<?php
/**
 * Foliokit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Activities;

use EasyDocLabs\Library;

/**
 * Searchable Model Behavior.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Activities
 */

class ModelBehaviorSearchable extends Library\ModelBehaviorSearchable
{
    /**
     * Overridden to dynamically add IP as searchable column when the search state value contains
     * the #ip: prefix.
     */
    protected function _buildQuery(Library\ModelContextInterface $context)
    {
        $state = $context->getState();
        $search = $state->search;

        if ($search && !$state->isUnique())
        {
            if (strpos($search, '#ip:') === 0)
            {
                if (!in_array('ip', $this->_columns)) {
                    array_push($this->_columns, 'ip');
                }

                $state->search = str_replace('#ip:', '', $search); // cleanup for search
            }
        }

        parent::_buildQuery($context);

        if ($state->search != $search) {
            $state->search = $search; // reset search state value
        }
    }

    /**
     * Resets the columns property by making sure that ip if removed when the state gets reset.
     */
    protected function _afterReset(Library\ModelContextInterface $context)
    {
        $reset_columns = false;

        if ($context->modified)
        {
            if (in_array('search', $context->modified->toArray())) {
                $reset_columns = true;
            }
        }
        else $reset_columns = true;

        if ($reset_columns && ($key = array_search('ip', $this->_columns))) {
            unset($this->_columns[$key]);
        }
    }
}