<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;

class ModelState extends Library\ModelState
{
    protected static $_internal = [
        'access', 'access_raw', // access level
        'include_self', 'level', // category related
        'enabled', 'published', 'status', // publishing information
        'page', 'Itemid', 'user', 'access', // current page and user
        'offset', 'category', 'category_children', 'storage_type', 'created_by',
        'storage_path', 'search_path', 'search_by', 'search_date', 'day_range'
    ];

    public function insert($name, $filter, $default = null, $unique = false, $required = [], $internal = false)
    {
        if (in_array($name, self::$_internal)) {
            $internal = true;
        }

        return parent::insert($name, $filter, $default, $unique, $required, $internal);
    }
}