<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelEntityRowset extends Library\ModelEntityRowset
{
    public function __call($method, $arguments)
    {
        if (strpos($method, 'can') === 0)
        {
            $result = false;

            foreach ($this as $row)
            {
                $result = call_user_func([$row, $method], $arguments);

                if (!$result) break;
            }
        }
        else $result = parent::__call($method, $arguments);

        return $result;
    }
}