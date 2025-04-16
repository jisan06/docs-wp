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
 * CAUTION: Hacky code ahead
 *
 * Hello there fellow developer,
 *
 * This model fetches all document tags found in the passed menu item.
 *
 * This whole class is one big hack but it works very well.
 * The hacks are explained inline. Be careful when you are editing something here.
 * Monsters can come out!
 *
 * Sincerely,
 * Your fellow developer
 */

class ModelPagetags extends ModelDocuments
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            // We extend from the documents model to use its states and the page filter methods
            // but this model returns tags. Hence:
            'table' => 'tags'
        ]);

        parent::_initialize($config);
    }

    protected function _buildQueryJoins(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryJoins($query);

        // Note: this is here because _buildQueryColumns is not called on count queries
        if (!$query->isCountQuery()) {
            $query->columns = []; // KModelDatabase::_actionFetch adds tbl.* by default, however we want tags.*
            $query->columns('tags.*');
        }

        $query->table   = []; // KModelDatabase::_actionFetch adds easydoc_tags AS tbl by default, we don't want that
        $query->table(['tbl' => 'easydoc_documents']);

        // This is the usual tag join
        $query->join('easydoc_tags_relations AS tags_relations', 'tags_relations.row = tbl.uuid');
        $query->join('easydoc_tags AS tags', 'tags.tag_id = tags_relations.tag_id');
    }

    protected function _buildQueryWhere(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryWhere($query);

        // If the menu item is filtered by tags already, we have an additional group by added in the page filters
        // Let's remove that and add ours to return only distinct tags
        $query->group = [];

        if (!$query->isCountQuery()) {
            $query->group('tags.tag_id');
        }

        $query->where('tags.tag_id IS NOT NULL');
    }
}