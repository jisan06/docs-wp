<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use  EasyDocLabs\Component\Tags;
use EasyDocLabs\Library;

/**
 * Taggable Database Behavior
 *
 * @author  Arunas Mazeika <http://github.com/amazeika>
 * @package Koowa\Component\Tags
 */
class DatabaseBehaviorCategoryTaggable extends Tags\DatabaseBehaviorTaggable
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['column' => 'documents_count.uuid']);

        parent::_initialize($config);
    }

    protected function _beforeSelect(Library\DatabaseContext $context)
    {
        $query = $context->getQuery();

        foreach ($query->columns as $alias => $column)
        {
            if ($alias == '_documents_count' && $column instanceof Library\DatabaseQuerySelect)
            {
                $clone = clone $context; // Make a copy for parent taggable

                $clone->query = $column; // Push subquery as main query

                parent::_beforeSelect($clone); // Add tags filtering conditions
            }
        }
    }
}