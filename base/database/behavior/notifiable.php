<?php

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class DatabaseBehaviorNotifiable extends Library\DatabaseBehaviorAbstract
{
    public function getNotifications($filter = [])
    {
        $notifications = null;

        if ($entity = $this->_getEntity())
        {
            $model = $this->getObject('com:easydoc.model.notifications');

            $model->row($entity->id);

            $table = $this->_getTable();

            $model->table($table->getName());

            if ($table->isNestable()) {
                $filter['relations_table'] = $table->getRelationTable();
            }

            foreach ($filter as $state => $value) {
                $model->{$state}($value);
            }

            $notifications = $model->fetch();
        }

        return $notifications;
    }

    protected function _afterDelete(Library\DatabaseContextInterface $context)
    {
        if ($context->affected !== false) {
            $this->getObject('com:easydoc.model.notifications')->row($this->_getEntity($context)->id)->table($this->_getTable()->getName())->fetch()->delete();
        }
    }

    protected function _getTable()
    {
        $mixer = $this->getMixer();

        if ($mixer instanceof Library\ModelEntityInterface) {
            $mixer = $mixer->getTable();
        }

        return $mixer;
    }

    protected function _getEntity(Library\DatabaseContextInterface $context = null)
    {
        $entity = null;

        if (is_null($context))
        {
            $mixer = $this->getMixer();

            if ($mixer instanceof Library\ModelEntityInterface) {
                $entity = $mixer;
            }
        }
        else $entity = $context->data;

        return $entity;
    }
}