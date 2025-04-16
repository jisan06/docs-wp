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
 * Provides ordering support for closure tables by the help of another table
 */
class DatabaseBehaviorCategoryOrderable extends Library\DatabaseBehaviorAbstract
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'priority'  => self::PRIORITY_LOWEST,
        ]);

        parent::_initialize($config);
    }

    protected function _beforeSelect(Library\DatabaseContextInterface $context)
    {
        $query = $context->query;

        if ($query)
        {
            $params    = $context->query->params;
            $id_column = $context->getSubject()->getIdentityColumn();

            // To display the custom ordering in backend
            if (!$query->isCountQuery())
            {
                $query->columns(['ordering' => 'ordering2.custom'])
                    ->join(['ordering2' => 'easydoc_category_orderings'], 'tbl.' . $id_column . ' = ordering2.' . $id_column, 'left');
            }

            // Force the sort if we are not fetching immediate children of a category
            if ($params && !(in_array($params->level, [1, [1]]) && $params->sort !== 'ordering'))
            {
                $column_map = ['title' => 'title', 'created_on' => 'created_on', 'ordering' => 'custom'];

                if (in_array($params->sort, array_keys($column_map)))
                {
                    $query->order = [];

                    $column = sprintf('GROUP_CONCAT(LPAD(`ordering`.`%s`, 5, \'0\') ORDER BY crumbs.level DESC  SEPARATOR \'/\')', $column_map[$params->sort]);

                    $query->join(['ordering' => 'easydoc_category_orderings'], 'crumbs.ancestor_id = ordering.' . $id_column, 'inner')
                        ->columns(['order_path' => $column])
                        ->order('order_path', 'ASC');
                }
            }
        }
    }

    protected function _afterInsert(Library\DatabaseContextInterface $context)
    {
        $entity    = $context->data;
        $siblings  = $entity->getSiblings();
        $orderings = [
            'title' => [],
            'created_on' => [],
            'custom' => []
        ];
        $custom_values = [];
        $sibling_ids   = [];

        foreach ($siblings as $sibling) {
            $sibling_ids[] = $sibling->id;
        }

        if ($sibling_ids) {
            $orders = $this->getObject('com:easydoc.model.category_orderings')
                ->id($sibling_ids)->sort('custom')->direction('asc')->fetch();
        } else {
            $orders = [];
        }

        foreach ($orders as $order) {
            $custom_values[$order->id] = $order->custom;
        }

        $next_order = ($custom_values ? max($custom_values) : 0)+1;

        foreach ($siblings as $child)
        {
            $orderings['title'][$child->id] = $child->title;
            $orderings['created_on'][$child->id] = $child->created_on;
            $orderings['custom'][$child->id] = isset($custom_values[$child->id]) ? $custom_values[$child->id] : $next_order++;
        }

        if ($entity->order)
        {
            // Pre-sort custom values
            asort($orderings['custom']);

            $ids = array_keys($orderings['custom']);
            $position = array_search($entity->id, $ids);
            $newPosition = $position + $entity->order;

            $temp = array_flip($orderings['custom']);

            foreach($temp as $i => $custom) {

               if($custom == $entity->id) {
                 unset($temp[$i]);
                 break;
               }
            }

            $temp = array_values($temp);

            array_splice($temp, $newPosition, 0, $entity->id);

            //start array from 1 instead of 0
            $temp = array_combine(range(1, count($temp)), array_values($temp));

            $orderings['custom'] = array_flip($temp);
        }

        // Sort before saving orders
        foreach ($orderings as $key => &$array)
        {
            if ($key === 'title') {
                $array = array_map('strtolower', $array);
            }

            asort($array, SORT_REGULAR);
        }

        foreach ($siblings as $item)
        {
            $order = $orders->find($item->id);

            if (!$order)
            {
                $order = $orders->create();
                $order->id = $item->id;
            }

            foreach (array_keys($orderings) as $key) {
                $order->{$key} = array_search($item->id, array_keys($orderings[$key])) + 1;
            }

            $order->save();
        }
    }

    protected function _afterUpdate(Library\DatabaseContextInterface $context)
    {
        $this->_afterInsert($context);
    }

    protected function _afterDelete(Library\DatabaseContextInterface $context)
    {
        $this->getObject('com:easydoc.model.category_orderings')
            ->id($context->data->id)
            ->fetch()
            ->delete();
    }
}
