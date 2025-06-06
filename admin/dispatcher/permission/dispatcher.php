<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Library;

class DispatcherPermissionDispatcher extends Library\DispatcherPermissionAbstract
{
    public function canDispatch()
    {
        $result = true;

        $view = $this->getObject('request')->getQuery()->view;

        $pages = ControllerToolbarMenubar::getPages();

        foreach ($pages as $page)
        {
            parse_str($page['route'], $parts);

            if (isset($parts['view']) && $parts['view'] == $view)
            {
                $permission = $page['permission'] ?? 'read';

                $result = is_callable($permission) ? $page['permission']() : $this->getObject('user')
                                                                                  ->capable($permission);
            }
        }

        return $result;
    }
}

