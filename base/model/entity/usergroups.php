<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelEntityUsergroups extends Library\ModelEntityRowset
{
    public function getIds()
    {
        $result = [];

        foreach ($this as $row) {
            $result[] = $row->id;
        }

        return $result;
    }
}