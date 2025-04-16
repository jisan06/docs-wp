<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

class ControllerToolbarMenubar extends Base\ControllerToolbarMenubar
{
    public static function getPages()
    {
        $manager = \Foliokit::getObject('manager');

        $t = $manager->getObject('translator');

        $canManage = function() use ($manager) {
            return $manager->getObject('com:easydoc.model.configs')->fetch()->canManage();
        };

        $canConfigure = function() use ($manager) {
            return $manager->getObject('com:easydoc.model.configs')->fetch()->canConfigure();
        };

        return [
            [
                'title' => $t('EasyDoc Submenu Documents'),
                'page'  => 'easydoc-documents',
                'route' => 'component=easydoc&view=documents&page=easydoc-documents',
                'permission' => $canManage
            ],
            [
                'title' => $t('EasyDoc Submenu Categories'),
                'page'  => 'easydoc-categories',
                'route' => 'component=easydoc&view=categories&page=easydoc-categories',
                'permission' => $canManage
            ],
            [
                'title' => $t('EasyDoc Submenu Tags'),
                'page'  => 'easydoc-tags',
                'route' => 'component=easydoc&view=tags&page=easydoc-tags',
                'permission' => $canConfigure
            ],
            [
                'title' => $t('EasyDoc Submenu Groups'),
                'page'  => 'easydoc-usergroups',
                'route' => 'component=easydoc&view=usergroups&page=easydoc-usergroups',
                'permission' => $canConfigure
            ],
            [
                'title' => $t('EasyDoc Submenu Settings'),
                'page'  => 'easydoc-settings',
                'route' => 'component=easydoc&view=config&page=easydoc-settings',
                'permission' => $canConfigure
            ]
        ];
    }

    public function getCommands()
    {
        $name = $this->getController()->getIdentifier()->name;

        $user = $this->getObject('user');

        foreach (self::getPages() as $page)
        {
            parse_str($page['route'], $query);

            $query['page'] = $page['page'];

            $permission = $page['permission'] ?? 'read';

            if (is_callable($permission) ? $permission() : $user->capable($permission))
            {
                $this->addCommand($page['title'], [
                    'href'   => $page['route'],
                    'active' => isset($query['view']) && ($name == Library\StringInflector::singularize($query['view']))
                ]);
            }
        }

        return parent::getCommands();
    }
}
