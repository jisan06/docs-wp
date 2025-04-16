<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class DatabaseBehaviorDocumentNotifiable extends DatabaseBehaviorNotifiable
{
    protected function _getEntity(Library\DatabaseContextInterface $context = null)
    {
        if (($entity = parent::_getEntity($context))) {
            $entity = $entity->category;
        }

        return $entity;
    }

    protected function _getTable()
    {
        return $this->getObject('com:easydoc.database.table.categories');
    }

    protected function _afterDelete(Library\DatabaseContextInterface $context)
    {
        // Do nothing
    }
}