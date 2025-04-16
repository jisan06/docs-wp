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
 * Usergroups inheritable database behavior class
 *
 * Handles usergroups access inheritance synchronisation when updating or creating categories
 *
 * @package EasyDocLabs\EasyDoc
 */
class DatabaseBehaviorCategoryGroupInheritable extends DatabaseBehaviorInheritable
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(array('column' => 'inherit_category_group_access'));

        parent::_initialize($config);
    }

    protected function _isInheritable(Library\ModelEntityInterface $entity)
    {
        $result = false;

        if (!$entity->isNew()) {
            $result = !!$entity->getCategoryGroupAccess();
        }

        return $result;
    }
}
