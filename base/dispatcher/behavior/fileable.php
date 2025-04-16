<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */   

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class DispatcherBehaviorFileable extends Library\DispatcherBehaviorAbstract
{
    protected function _setContainer(Library\DispatcherContextInterface $context)
    {
        $query = $context->request->query;

        if (!in_array($query->container, ['easydoc-files', 'easydoc-icons', 'easydoc-images'])) {
            $query->container = 'easydoc-files';
        }

        $container = $this->getObject('com:files.model.containers')
            ->slug($query->container)
            ->fetch();

        if (!is_dir($container->fullpath))
        {
            throw new \RuntimeException($this->getObject('translator')->translate(
                'Document path is missing. Please make sure there is a folder named {folder} on your site root.', [
                'folder' => $container->path
            ]));
        }

        if ($query->layout === 'select')
        {
            $query->types = ['image'];

            if ($query->container === 'easydoc-files') {
                $query->types = ['image', 'file'];
            }
        }
    }

    protected function _attachBehaviors(Library\DispatcherContextInterface $context)
    {
        if ($context->request->query->container === 'easydoc-icons')
        {
            $this->getIdentifier('com:files.controller.file')->getConfig()->append([
                'behaviors' => ['com://admin/easydoc.controller.behavior.resizable']
            ]);
        }

        // Use our own ACL and cache the hell out of JSON requests
        $behaviors = [
            'permissible' => [
                'permission' => 'com:easydoc.controller.permission.file'
            ]
        ];

        if ($context->request->query->container === 'easydoc-files')
        {
            foreach (['files', 'folders', 'nodes'] as $name)
            {
                $this->getIdentifier('com:files.model.'.$name)->getConfig()->append([
                    'behaviors' => 'com:easydoc.model.behavior.fileable'
                ]);
            }

            $behaviors[] = 'com:easydoc.controller.behavior.movable';
            $behaviors[] = 'com:easydoc.controller.behavior.syncable';
        }

        foreach (['file', 'folder', 'node', 'proxy', 'thumbnail', 'container'] as $name)
        {
            $this->getIdentifier('com:files.controller.'.$name)->getConfig()->append([
                'behaviors' => $behaviors
            ]);
        }
    }

    protected function _beforeDispatch(Library\DispatcherContext $context)
    {
        $query = $context->request->query;

        if ($query->routed ||
            ($query->view === 'files' && (!$query->has('layout') || in_array($query->layout, ['default', 'select']))))
        {
            $layout = $query->layout;

            $this->_setContainer($context);
            $this->_attachBehaviors($context);

            $config = [
                'grid' => [
                    'layout' => ($layout === 'select' ? 'compact' : 'details')
                ],
                'router' => [
                    'defaults' => []
                ]
            ];

            /*if ($menu = JFactory::getApplication()->getMenu()->getActive())
            {
                $base_path = $context->request->getUrl()->toString(Library\HttpUrl::AUTHORITY);
                $menu_path = JRoute::_('index.php?option=com_easydoc&Itemid='.$menu->id, false);
                $menu_path = $this->getObject('filter.factory')->createChain('url')->sanitize($menu_path);

                $config['base_url'] = $base_path.$menu_path;
                $config['router']['defaults']['Itemid'] = $menu->id;
            }*/

            $query->config = $config;
            $query->layout = $layout === 'select' ? 'compact' : 'default';

            $config = [
                'router'     => $context->router,
                'request'    => $context->request,
                'response'   => $context->response,
                'user'       => $context->user,
                'forwarded'  => $this
            ];

            $dispatcher = $this->getObject('com:files.dispatcher', $config);
            $dispatcher->dispatch($context);

            if ($query->routed) {
                $dispatcher->send();
            }

            $query->layout = $layout;

            // Set the layout back for the EasyDocview
            $this->getObject('request')->query->layout = $layout;
        }
    }
}
