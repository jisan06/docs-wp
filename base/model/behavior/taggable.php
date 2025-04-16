<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Component\Tags;
use EasyDocLabs\Library;

class ModelBehaviorTaggable extends Tags\ModelBehaviorTaggable
{
    public function onMixin(Library\ObjectMixable $mixer)
    {
        parent::onMixin($mixer);

        //Insert the tag model state
        $mixer->getState()->remove('tag')->insert('tag', 'slug');
    }

    protected function _afterFetch(Library\ModelContextInterface $context)
    {
        $entities = $context->entity;

        if (is_countable($entities) && count($entities))
        {
            $ids = [];

            foreach ($entities as $entity) {
                $ids[] = $entity->uuid;
            }

            $query = $this->getObject('lib:database.query.select')
                ->columns([
                    'row' => 'tags_relations.row',
                    'tags_linked' => "GROUP_CONCAT(DISTINCT CONCAT('{', tbl.slug, '}', tbl.title, '{/}') ORDER BY tbl.title ASC SEPARATOR ', ')",
                    'tags' => "GROUP_CONCAT(DISTINCT tbl.title ORDER BY tbl.title ASC SEPARATOR ', ')"
                ])
                ->table('easydoc_tags AS tbl')
                ->join('easydoc_tags_relations AS tags_relations', 'tags_relations.tag_id = tbl.tag_id')
                ->where('tags_relations.row IN :id')
                ->group('tags_relations.row')
                ->bind(['id' => $ids]);

            $map = $this->getTable()->getDriver()->select($query, Library\Database::FETCH_OBJECT_LIST, 'row');

            foreach ($entities as $entity) {
                if (isset($map[$entity->uuid])) {
                    $entity->tag_list = $map[$entity->uuid]->tags;
                    $entity->tag_list_linked = $map[$entity->uuid]->tags_linked;
                }
            }
        }
    }
}
