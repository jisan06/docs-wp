<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelEntityLevel extends Library\ModelEntityRow
{
    protected static $_groups;

    public function toArray()
    {
        $data               = parent::toArray();
        $data['group_list'] = $this->getGroups();

        return $data;
    }

    public function getGroups()
    {
        if (!self::$_groups)
        {
            $query = $this->getObject('database.query.select')->columns(['title', 'id']);
            $table = $this->getObject('com:easydoc.database.table.usergroups', [
                'name' => 'usergroups'
            ]);
            $groups = $table->select($query, Library\Database::FETCH_OBJECT_LIST);

            self::$_groups = array_map(function($object) { return $object->title; }, $groups);
        }

        $result = [];
        $groups = explode(',', $this->groups);

        if ($groups) {
            $result = array_intersect_key(self::$_groups, array_flip($groups));
        }

        return $result;
    }
}