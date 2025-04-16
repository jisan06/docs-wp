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
 * Calendar Filter
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Library\Filter
 */
class FilterCalendar extends Library\FilterAbstract implements Library\FilterTraversable
{
    public function validate($value)
    {
        $result = false;

        $filters = ['timestamp', 'date', 'time'];

        foreach ($filters as $filter) {
            if ($result = $this->getObject('lib:filter.' . $filter)->validate($value)) break;
        }

        return $result;
    }

    public function sanitize($value)
    {
        $value = trim($value);

        if (!$this->validate($value)) $value = null;

        return $value;
    }
}