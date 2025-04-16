<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelEntityNotifications extends Library\ModelEntityRowset
{
    public function toArray()
    {
        $notifications = [];

        foreach ($this as $notification) $notifications[] = $notification->toArray();

        return $notifications;
    }
}