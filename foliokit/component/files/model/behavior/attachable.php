<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Files;

use EasyDocLabs\Library;

/**
 * Attachable Model behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ModelBehaviorAttachable extends ModelBehaviorRelatable
{
    /**
     * Overridden to include creatable info.
     */
    protected function _beforeFetch(Library\ModelContextInterface $context)
    {
        parent::_beforeFetch($context);

        if ($context->getName() != 'before.count')
        {
            $state = $context->getState();
            $query = $context->query;

            if ($state->table || $state->row)
            {
                $query->join('users AS users', 'relations.created_by = users.id', 'LEFT');

                $query->columns([
                    'attached_by'      => 'relations.created_by',
                    'attached_on'      => 'relations.created_on',
                    'attached_by_name' => 'users.name'
                ]);
            }
        }
    }
}