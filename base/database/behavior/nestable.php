<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2011 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

/**
 * Companion behavior for the node row
 *
 * This behavior is used for saving and deleting relations. A separate behavior is used to make sure that other behaviors
 * like orderable can use methods like getAncestors, getParent.
 */
class DatabaseBehaviorNestable extends Library\DatabaseBehaviorAbstract
{
    /**
     * Constant to fetch all levels in traverse methods
     *
     * @var int
     */
    const FETCH_ALL_LEVELS = 0;

    /**
     * We do not run afterDelete event for rows in this array
     * since they will be taken care of by their parent row.
     *
     * @var array
     */
    protected static $_to_be_deleted = [];

    protected $_relation_table;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        if (empty($config->relation_table)) {
            throw new \InvalidArgumentException('Relation table cannot be empty');
        }

        $this->setRelationTable($config->relation_table);
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'priority' => self::PRIORITY_HIGHEST,
        ]);

        parent::_initialize($config);
    }

    /**
     * Get relatives of the row
     *
     * @param string $type ancestors or descendants
     * @param int    $level Filters results by the level difference between ancestor and the row, self::FETCH_ALL_LEVELS for all
     * @param int    $mode    The database fetch style.
     *
     * @throws InvalidArgumentException
     * @return KModelEntityInterface
     */
    public function getRelatives($type, $level = self::FETCH_ALL_LEVELS, $mode = Library\Database::FETCH_ROWSET)
    {
        if (empty($type) || !in_array($type, ['ancestors', 'descendants'])) {
            throw new InvalidArgumentException('Unknown type value');
        }

        if (!$this->id && $type === 'ancestors') {
            return $this->getTable()->createRowset();
        }

        $id_column = $this->getTable()->getIdentityColumn();

        $join_column  = $type === 'ancestors' ? 'r.ancestor_id' : 'r.descendant_id';
        $where_column = $type === 'ancestors' ? 'r.descendant_id' : 'r.ancestor_id';

        $query = $this->getObject('lib:database.query.select')
            ->columns('tbl.*')
            ->columns(['level' => 'COUNT(crumbs.ancestor_id)'])
            ->columns(['path' => 'GROUP_CONCAT(crumbs.ancestor_id ORDER BY crumbs.level DESC SEPARATOR \'/\')'])
            ->table(['tbl' => $this->getTable()->getName()])
            ->join(['crumbs' => $this->getRelationTable()], 'crumbs.descendant_id = tbl.' . $id_column, 'inner')
            ->group('tbl.' . $id_column)
            ->order('path', 'ASC');

        if ($level !== self::FETCH_ALL_LEVELS)
        {
            if ($this->id) {
                $query->where('r.level IN :level');
            } else {
                $query->having('level IN :level');
            }
            $query->bind(['level' => (array)$level]);
        }

        if ($this->id)
        {
            $query->join(['r' => $this->getRelationTable()], $join_column . ' = crumbs.descendant_id', 'inner')
                ->where($where_column . ' IN :id')
                ->where('tbl.easydoc_category_id NOT IN :id')
                ->bind(['id' => (array)$this->id]);
        }

        $this->getTable()->getCommandChain()->setEnabled(false);
        $result = $this->getTable()->select($query, $mode);
        $this->getTable()->getCommandChain()->setEnabled(true);

        return $result;
    }

    /**
     * Returns the siblings of the row
     *
     * @return Library\ModelEntityInterface
     */
    public function getSiblings()
    {
        $parent = $this->getParent();

        return $parent && !$parent->isNew() ? $parent->getDescendants(1) : $this->getTable()->createRow()->getDescendants(1);
    }

    /**
     * Returns the first ancestor of the row
     *
     * @return Library\ModelEntityInterface|null Parent row or null if there is no parent
     */
    public function getParent()
    {
        return $this->getRelatives('ancestors', 1);
    }

    /**
     * Get ancestors of the row
     *
     * @param int $level Filters results by the level difference between ancestor and the row, self::FETCH_ALL_LEVELS for all
     *
     * @return Library\ModelEntityInterface A rowset containing all ancestors
     */
    public function getAncestors($level = self::FETCH_ALL_LEVELS)
    {
        return $this->getRelatives('ancestors', $level);
    }

    /**
     * Get descendants of the row
     *
     * @param int|array $level Filters results by the level difference between descendant and the row, self::FETCH_ALL_LEVELS for all
     *
     * @return Library\ModelEntityInterface A rowset containing all descendants
     */
    public function getDescendants($level = self::FETCH_ALL_LEVELS)
    {
        return $this->getRelatives('descendants', $level);
    }

    /**
     *
     * Move the row and all its descendants to a new position
     *
     * @link http://www.mysqlperformanceblog.com/2011/02/14/moving-subtrees-in-closure-table/
     *
     * @param  int $id        Row id
     * @param  int $target_id Target to move the subtree under
     * @return boolean Result of the operation
     */
    public function move($id, $target_id)
    {
        $query = 'DELETE a FROM #__%1$s AS a'
            . ' JOIN #__%1$s AS d ON a.descendant_id = d.descendant_id'
            . ' LEFT JOIN #__%1$s AS x ON x.ancestor_id = d.ancestor_id AND x.descendant_id = a.ancestor_id'
            . ' WHERE d.ancestor_id = %2$d AND x.ancestor_id IS NULL';

        $result = $this->getTable()->getDriver()->execute(sprintf($query, $this->getRelationTable(), $id));

        $query = 'INSERT INTO #__%1$s (ancestor_id, descendant_id, level)'
            . ' SELECT a.ancestor_id, b.descendant_id, a.level+b.level+1'
            . ' FROM #__%1$s AS a'
            . ' JOIN #__%1$s AS b'
            . ' WHERE b.ancestor_id = %2$d AND a.descendant_id = %3$d';

        $result = $this->getTable()->getDriver()->execute(sprintf($query, $this->getRelationTable(), $id, $target_id));

        return $result;
    }

    /**
     * Get parent id
     *
     * @return int|null The parent id if row has a parent, null otherwise.
     */
    public function getParentId()
    {
        $ids = array_values($this->getParentIds());

        return $this->level > 1 ? end($ids) : null;
    }

    /**
     * Get parent ids
     *
     * @return array The parent ids.
     */
    public function getParentIds()
    {
        $ids = array_map('intval', explode('/', $this->path ?: ''));
        array_pop($ids);

        return $ids;
    }

    /**
     * Checks if the given row is a descendant of this one
     *
     * @param  int|object $target Either an integer or an object with id property
     * @return boolean
     */
    public function hasAncestor($target)
    {
        $target_id = is_object($target) ? $target->id : $target;

        return $this->_checkRelationship($this->id, $target_id);
    }

    /**
     * Checks if the given row is an ancestor of this one
     *
     * @param  int|object $target Either an integer or an object with id property
     * @return boolean
     */
    public function hasDescendant($target)
    {
        $target_id = is_object($target) ? $target->id : $target;

        return $this->_checkRelationship($target_id, $this->id);
    }

    /**
     * Checks if an ID is descendant of another
     *
     * @param int $descendant Descendant ID
     * @param int $ancestor Ancestor ID
     *
     * @return boolean True if descendant is a child of the ancestor
     */
    protected function _checkRelationship($descendant, $ancestor)
    {
        if (empty($this->id)) {
            return false;
        }

        $query = $this->getObject('lib:database.query.select');
        $query->columns('COUNT(*)')
            ->table(['r' => $this->getRelationTable()])
            ->where('r.descendant_id = :descendant_id')->bind(['descendant_id' => (int)$descendant])
            ->where('r.ancestor_id = :ancestor_id')->bind(['ancestor_id' => (int)$ancestor]);

        $this->getTable()->getCommandChain()->setEnabled(false);
        $result = (bool)$this->getTable()->select($query, Library\Database::FETCH_FIELD);
        $this->getTable()->getCommandChain()->setEnabled(true);

        return $result;
    }

    protected function _beforeSelect(Library\DatabaseContextInterface $context)
    {
        $query  = $context->query;
        $params = $context->query->params;

        if (!$query) {
            return true;
        }

        $is_count = false;
        if ($query->isCountQuery() && $context->mode === Library\Database::FETCH_FIELD) {
            $is_count = true;
            $query->columns = [];
        }

        $id_column     = $context->getSubject()->getIdentityColumn();
        $closure_table = $this->getRelationTable();

        // We are going to force ordering ourselves here
        $query->order = [];
        $sort         = 'path';
        $direction    = 'ASC';

        $query->columns(['level' => 'COUNT(DISTINCT crumbs.ancestor_id)'])
            ->columns(['path' => 'GROUP_CONCAT(DISTINCT crumbs.ancestor_id ORDER BY crumbs.level DESC SEPARATOR \'/\')'])
            ->join(['crumbs' => $closure_table], 'crumbs.descendant_id = tbl.' . $id_column, 'INNER')
            ->group('tbl.' . $id_column);

        if ($max_level = (int) $params->get('max_level')) {
            $params->set('level', range(1, $max_level));
        }

        if ($params->has('parent_id'))
        {
            $query->join(['closures' => $closure_table], 'closures.descendant_id = tbl.' . $id_column, 'inner')
                ->where('closures.ancestor_id IN :parent_id')
                ->bind(['parent_id' => (array)$params->get('parent_id')]);

            if (!$params->has('include_self')) {
                $query->where('tbl.' . $id_column . ' NOT IN :parent_id');
            }

            if ($params->has('level')) {
                $query->where('closures.level IN :level')->bind(['level' => (array)$params->get('level')]);
            }

        } elseif ($params->has('level')) {
            $query->having('level IN :level')->bind(['level' => (array)$params->get('level')]);
        }

        // If we are fetching the immediate children of a category we can sort however we want
        if (in_array($params->level, [1, [1]]) && $params->sort !== 'custom')
        {
            $sort      = $params->sort ? ('tbl.' . $params->sort) : $sort;
            $direction = $params->direction ?: $direction;
        }

        $query->order($sort, $direction);

        if ($is_count) {
            $data          = $context->getSubject()->getDriver()->select($context->query, Library\Database::FETCH_FIELD_LIST);
            $context->data = count($data);

            return false;
        }

        return true;
    }

    protected function _afterInsert(Library\DatabaseContextInterface $context)
    {
        if ($context->affected !== false) {
            $this->_saveRelations($context);
        }
    }

    protected function _afterUpdate(Library\DatabaseContextInterface $context)
    {
        $this->_saveRelations($context);
    }

    protected function _beforeDelete(Library\DatabaseContextInterface $context)
    {
        if (!in_array($this->id, self::$_to_be_deleted))
        {
            foreach ($this->getDescendants() as $descendant) {
                self::$_to_be_deleted[] = $descendant->id;
            }
        }
    }

    /**
     * Deletes the row, its children and its node relations
     *
     * @param Library\DatabaseContextInterface
     */
    protected function _afterDelete(Library\DatabaseContextInterface $context)
    {
        if ($context->affected)
        {
            if (!in_array($context->data->id, self::$_to_be_deleted))
            {
                $descendants = $this->getDescendants();
                $ids         = [];

                foreach ($descendants as $descendant) {
                    $ids[] = $descendant->id;
                }

                $ids[] = $context->data->id;

                $descendants->delete();

                $query = $this->getObject('lib:database.query.delete')
                    ->table($this->getRelationTable())
                    ->where('descendant_id IN :id')
                    ->bind(['id' => $ids]);
                $this->getTable()->getDriver()->execute($query);
            }
        }
    }

    /**
     * Saves the row hierarchy to the relations table
     *
     * @param Library\DatabaseContextInterface $context
     * @return bool
     */
    protected function _saveRelations(Library\DatabaseContextInterface $context)
    {
        $entity  = $context->data;

        if ($context->query instanceof Library\DatabaseQueryInsert)
        {
            $query = sprintf('INSERT INTO #__%s (ancestor_id, descendant_id, level)
                SELECT ancestor_id, %d, level+1 FROM #__%1$s
                WHERE descendant_id = %d
                UNION ALL SELECT %2$d, %2$d, 0
                ', $this->getRelationTable(), $entity->id, (int)$entity->parent_id);

            $this->getTable()->getDriver()->execute($query);
        }
        else
        {
            if ($entity->parent_id && $entity->hasDescendant($entity->parent_id))
            {
                $translator = $this->getObject('translator');
                $this->setStatusMessage($translator->translate('You cannot move a node under one of its descendants'));
                $this->setStatus(Library\Database::STATUS_FAILED);

                return false;
            }

            // Check if parent_id is the same in the relation table and the data table
            $parent  = $entity->getParent();

            if ($entity->isModified('parent_id') && (!$parent || $entity->parent_id != $parent->id)){
                $this->move($entity->id, $entity->parent_id);
            }
        }

        return true;
    }

    public function getRelationTable()
    {
        return $this->_relation_table;
    }

    public function setRelationTable($table)
    {
        $this->_relation_table = $table;

        return $this;
    }
}
