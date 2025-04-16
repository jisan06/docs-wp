<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

/**
 * Paginatable Model Behavior
 *
 * This class is overridden to remove the offset recalculation for page start logic
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Model\Behavior
 */

class ModelBehaviorPaginatable extends Library\ModelBehaviorPaginatable
{
    /**
     * Add limit query
     *
     * @param   Library\ModelContextInterface $context A model context object
     * @return  void
     */
    protected function _beforeFetch(Library\ModelContextInterface $context)
    {
        $model = $context->getSubject();

        if ($model instanceof Library\ModelDatabase && !$context->state->isUnique())
        {
            $state = $context->state;
            $limit = $state->limit;

            if ($limit)
            {
                $offset = $state->offset;
                $total  = $this->count();

                if ($offset !== 0 && $total !== 0)
                {
                    // Recalculate the offset if it is set to the middle of a page.
                    if ($offset % $limit !== 0) {
                        //    $offset -= ($offset % $limit);
                    }

                    // Recalculate the offset if it is higher than the total
                    if ($offset >= $total) {
                        $offset = floor(($total - 1) / $limit) * $limit;
                    }

                    $state->offset = $offset;
                }

                $context->query->limit($limit, $offset);
            }
        }
    }
}