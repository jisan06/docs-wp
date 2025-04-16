<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelCategories extends ModelAbstract
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
             ->insert('folder', 'string')
             ->insert('created_by', 'int')
             ->insert('enabled', 'int')
             ->insert('tag', 'slug')
             ->insert('hide_empty', 'boolean', false, false, [], true)
             ->insert('documents_count', 'boolean', false, false, [], true);
	}

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors' => [
                'nestable',
                'com:easydoc.model.behavior.category.permissible',
                'searchable' => ['columns' => ['title', 'description']]
            ]
        ]);

        parent::_initialize($config);
    }

    protected function _buildQueryColumns(Library\DatabaseQueryInterface $query)
    {
        $state = $this->getState();

        $query->columns(array('folder' => 'folder.folder'));
        $query->columns(array('automatic_folder' => 'folder.automatic'));

        if ($state->documents_count || $state->hide_empty) {
            $query->columns(array('_documents_count' => $this->_getDocumentsCountSubquery()));
        }

        parent::_buildQueryColumns($query);
    }

    protected function _buildQueryJoins(Library\DatabaseQueryInterface $query)
    {
        $state = $this->getState();

        $query->join(['folder' => 'easydoc_category_folders'], 'tbl.easydoc_category_id = folder.easydoc_category_id');

        parent::_buildQueryJoins($query);
    }

    protected function _buildQueryWhere(Library\DatabaseQueryInterface $query)
    {
        parent::_buildQueryWhere($query);

        $state = $this->getState();

        if (is_numeric($state->enabled)) {
            $query->where('tbl.enabled = :enabled')
                ->bind(['enabled' => $state->enabled]);
        }

        if ($state->created_by)
        {
            $query->where('tbl.created_by IN :created_by')
                ->bind(['created_by' => (array) $state->created_by]);
        }

        if ($state->folder)
        {
            $query->where('folder.folder IN :folder')->bind([
                'folder' => (array) $state->folder
            ]);
        }
    }

    protected function _buildQuery(Library\ModelContextDatabase $context)
    {
        parent::_buildQuery($context);

        $state = $context->state;

        if($context->action == 'count')
        {
            if ($state->hide_empty)
            {
                $subquery = $this->_getDocumentsCountSubquery();

                $context->query->where(sprintf('(%s > 0)', $subquery->toString()));
            }
        }
        elseif ($context->action == 'fetch')
        {
            if ($state->hide_empty) {
                $context->query->having('_documents_count > 0');
            }
        }
    }

    protected function _getDocumentsCountSubquery()
    {
		$subquery = $this->getObject('lib:database.query.select')
						->table(['documents_count' => 'easydoc_documents'])
						->columns('COUNT(DISTINCT(documents_count.easydoc_document_id))')
						->join('easydoc_categories AS categories_count', 'documents_count.easydoc_category_id = categories_count.easydoc_category_id', 'INNER')
						->join('easydoc_category_relations AS rels_count', 'rels_count.descendant_id = categories_count.easydoc_category_id', 'INNER')
						->where('rels_count.ancestor_id = tbl.easydoc_category_id');

		$this->setDocumentsCountQuery($subquery, '_count');

        if ($tags = $this->getState()->tag)
        {
            $this->getTable()->addBehavior('com:easydoc.database.behavior.category.taggable'); // Filter documents count by tags also
            $subquery->bind(['tag' => $tags]);
        }

		return $subquery;
    }
}
