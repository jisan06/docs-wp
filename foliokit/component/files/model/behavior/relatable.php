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
 * Relatable Model behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ModelBehaviorRelatable extends Library\ModelBehaviorAbstract
{
    /**
     * Relations model.
     *
     * @var Library\ModelInterface|string|Library\ObjectIdentifierInterface
     */
    protected $_model;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_model = $config->model;
        $this->_columns = Library\ObjectConfig::unbox($config->columns);
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        if ($mixer = $config->mixer) {
            $model = sprintf('%s_%s', $mixer->getIdentifier()->getName(), 'relations');
        } else {
            $model = 'relations';
        }

        $config->append(['model' => $model, 'columns' => ['table' => 'cmd', 'row' => 'cmd']]);
        parent::_initialize($config);
    }

    /**
     * Before Fetch command handler.
     *
     * Adds joins and where statements.
     *
     * @param Library\ModelContextInterface $context The context object.
     */
    protected function _beforeFetch(Library\ModelContextInterface $context)
    {
        $query = $context->query;

        $state = $context->getState();

        if (array_intersect(array_keys($state->getValues()), array_keys($this->_columns)))
        {
            $table  = $this->getRelationsModel()->getTable()->getBase();
            $column = $this->getTable()->getIdentityColumn();

            $query->join($table . ' AS relations', 'relations.' . $column . ' = tbl.' . $column, 'INNER');


            foreach (array_keys($this->_columns) as $column)
            {
                if ($state->{$column}) {
                    $query->where(sprintf('relations.%1$s = :%1$s', $column))->bind([$column => $state->{$column}]);
                }
            }
        }
    }

    /**
     * Before Count command handler.
     *
     * Adds joins and where statements.
     *
     * @param Library\ModelContextInterface $context The context object.
     */
    protected function _beforeCount(Library\ModelContextInterface $context)
    {
        $this->_beforeFetch($context); // Same as fetch.
    }

    /**
     * Insert the model states
     *
     * @param Library\ObjectMixable $mixer
     */
    public function onMixin(Library\ObjectMixable $mixer)
    {
        parent::onMixin($mixer);

        if ($mixer instanceof Library\ModelDatabase)
        {
            foreach ($this->_columns as $name => $filter) {
                $mixer->getState()->insert($name, $filter);
            }
        }
    }

    /**
     * Relations Model getter.
     *
     * @return Library\ModelInterface
     */
    public function getRelationsModel()
    {
        if (!$this->_model instanceof Library\ModelInterface)
        {
            $identifier = $this->_model;

            if (is_string($identifier))
            {
                if (strpos($identifier, '.') === false)
                {
                    $identifier = $this->getMixer()->getIdentifier()->toArray();
                    $identifier['name'] = $this->_model;
                }

                $identifier = $this->getIdentifier($identifier);
            }

            $this->_model = $this->getObject($identifier, [
                'relation_column' => $this->getTable()
                                          ->getIdentityColumn()
            ]);
        }

        return $this->_model;
    }
}