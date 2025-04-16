<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelDocuments extends ModelAbstract
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->insert('category', 'int')
            ->insert('category_children', 'boolean')
            ->insert('created_by', 'int')
            ->insert('created_on', 'string')
            ->insert('created_on_from', 'string')
            ->insert('created_on_to', 'string')
            ->insert('enabled', 'int')
            ->insert('status', 'cmd')
            ->insert('search', 'string')
            ->insert('storage_type', 'identifier')
            ->insert('storage_path', 'com:files.filter.path')
            ->insert('search_path', 'com:files.filter.path')
            ->insert('search_by', 'string', 'exact')
            ->insert('search_date', 'date')
            ->insert('search_contents', 'boolean', true)
            ->insert('image', 'com:files.filter.path')
            ->insert('day_range', 'int')
            ->insert('alias', 'string');
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors' => [
				'com:easydoc.model.behavior.document.permissible',
                'com:easydoc.model.behavior.taggable',
                'searchable' => ['columns' => ['title', 'description']]
            ]
        ]);

        parent::_initialize($config);
    }

    protected function _buildQueryColumns(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryColumns($query);

        $query->columns('c.title AS category_title')
            ->columns('c.slug AS category_slug')
            ->columns('c.enabled AS category_enabled')
            ->columns('c.created_by AS category_owner')
            ->columns('CONCAT_WS(\'-\', tbl.easydoc_document_id, tbl.slug) AS alias')
            ->columns('
                IF(tbl.enabled = 1,
                    IF(tbl.publish_on IS NOT NULL AND tbl.publish_on > :now,
                        :pending_value,
                        IF(tbl.unpublish_on IS NOT NULL AND :now > tbl.unpublish_on,
                            :expired_value,
                            :published_value
                        )
                    ),
                    :unpublished_value
                ) AS status')
            ->columns('IF(tbl.publish_on IS NULL, tbl.created_on, tbl.publish_on) AS publish_date')
            ->columns('GREATEST(tbl.created_on, tbl.modified_on) AS touched_on');
    }

    protected function _buildQueryJoins(Library\DatabaseQueryInterface $query)
    {
        $state = $this->getState();

    	$query->join(['c' => 'easydoc_categories'], 'tbl.easydoc_category_id = c.easydoc_category_id');

        parent::_buildQueryJoins($query);
    }

    protected function _buildQueryWhere(Library\DatabaseQueryInterface $query)
    {
        $state = $this->getState();

        parent::_buildQueryWhere($query);

        $query->bind([
            'now' => gmdate('Y-m-d H:i:s'),
            'published_value' => 'published',
            'unpublished_value' => 'unpublished',
            'expired_value' => 'expired',
            'pending_value' => 'pending',
        ]);


        $categories = (array) $state->category;
        if ($categories)
        {
            $include_children = $state->category_children;

            if ($include_children)
            {
                $query->join(['r' => 'easydoc_category_relations'], 'r.descendant_id = tbl.easydoc_category_id')
                    ->where('r.ancestor_id IN :include_children_categories')
                    ->bind(['include_children_categories' => $categories]);
            }
            else {
                $query->where('tbl.easydoc_category_id IN :include_children_categories')
                    ->bind(['include_children_categories' => $categories]);
            }
        }

        $conditions = '';

        if (is_numeric($state->enabled)) {
			$conditions = 'tbl.enabled = :enabled';
        }

        $now = $this->getObject('date')->format('Y-m-d H:i:s');

        if ($status = $state->status)
        {
            $conditions .= !empty($conditions) ? ' AND ' : '';

            if ($status === 'published') {
                $conditions .= '((tbl.publish_on IS NULL OR tbl.publish_on <= :publish_date) AND (tbl.unpublish_on IS NULL OR tbl.unpublish_on >= :publish_date))';
            } elseif ($status === 'pending') {
                $conditions .= '(tbl.publish_on IS NOT NULL AND tbl.publish_on >= :publish_date)';
            } elseif ($status === 'expired') {
                $conditions .= '(tbl.unpublish_on IS NOT NULL AND tbl.unpublish_on <= :publish_date)';
            }
        }

        if ($conditions)
        {
            if ($this->isPermissible()) {
                list($conditions, $data) = $this->setOwnerAccessConditions($conditions);
            }

            $query->where(sprintf('(%s)', $conditions))->bind([
                'enabled'      => (int) $state->enabled,
                'user'         => $state->user,
                'publish_date' => $now
            ]);

            if (isset($data)) $query->bind($data);
        }
        
        if ($created_on = $state->created_on)
        {
            static $format_map = [
                4  => '%Y', // 2014
                7  => '%Y-%m', // 2014-10
                10 => '%Y-%m-%d', // 2014-10-10
                13 => '%Y-%m-%d %H', // 2014-10-10 10
                16 => '%Y-%m-%d %H:%i', // 2014-10-10 10:10
                0  => '%Y-%m-%d %H:%i:%s' // 2014-10-10 10:10:10
            ];

            $format = isset($format_map[strlen($created_on)]) ? $format_map[strlen($created_on)] : $format_map[0];

            $query->where("DATE_FORMAT(tbl.created_on, '$format') = :created_on")
                  ->bind(['created_on' => $created_on]);
        }

        if ($state->created_on_from) {
            $query->where("tbl.created_on >= :created_on_from")
                ->bind(['created_on_from' => $state->created_on_from]);
        }

        if ($state->created_on_to)
        {
            $end_date = $state->created_on_to;
            // Add the hour if it's missing to make the date inclusive
            if (preg_match('#^[0-9]{4}\-[0-9]{2}-[0-9]{2}$#', $end_date)) {
                $end_date .= ' 23:59:59';
            }

            $query->where("tbl.created_on <= :created_on_to")
                ->bind(['created_on_to' => $end_date]);
        }

        if ($state->search_date || $state->day_range)
        {
            $date      = $state->search_date ? ':date' : ':now';
            $date_bind = $state->search_date ?: null;

            if ($state->day_range) {
              $query->where("(tbl.created_on BETWEEN DATE_SUB($date, INTERVAL :days DAY) AND DATE_ADD($date, INTERVAL :days DAY))")
            		    ->bind(['date' => $date_bind, 'days' => $state->day_range]);
            }
        }

        if (is_numeric($state->created_by) || !empty($state->created_by)) {
            $query->where('tbl.created_by IN :created_by')->bind(['created_by' => (array) $state->created_by]);
        }

        if ($state->storage_type) {
            $query->where('tbl.storage_type IN :storage_type')->bind(['storage_type' => (array) $state->storage_type]);
        }

        if ($image = $state->image) {
            $query->where('tbl.image IN :image')->bind(['image' => (array) $image]);
        }

        if ($state->storage_path) {
            $query->where('tbl.storage_path IN :storage_path')->bind(['storage_path' => (array) $state->storage_path]);
        }

        if ($state->search_path !== null)
        {
            if ($state->search_path === '')
            {
                $operation = 'NOT LIKE';
                $path = "%/%";
            }
            else
            {
                $operation = 'LIKE';
                $path = $state->search_path;
            }

            $query->where('tbl.storage_path '.$operation. ' :path')->bind(['path' => $path]);
        }

        if ($state->alias) {
            $query->where('tbl.slug IN :slug')->bind(['slug' => (array) $state->alias]);
        }
    }
}
