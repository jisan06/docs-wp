<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Component\Base;

class ControllerToolbarAdmin extends Base\ControllerToolbarAdmin
{
    public function getCommands()
    {
        $commands = [];
        $pages    = [];

        foreach (ControllerToolbarMenubar::getPages() as $page)
        {
            $permission = $page['permission'] ?? 'read';

            if (is_callable($permission))
            {
                if (!$permission()) continue;

                $page['permission'] = 'read';
            }

            $pages[] = $page;
        }

        if (count($pages))
        {
            // If first page has a route it leads to an infinite loop

            $page       = $pages[0]['page'];
            $route      = $pages[0]['route'];
            $permission = $pages[0]['permission'];

            unset($pages[0]['route']);

            $query = $this->getObject('request')->getQuery();

            if ($query->get('component', 'cmd') === 'easydoc' && $query->has('page')) {
                //$pages = [];
            }

            $commands = [
                [
                    'title'      => $this->getObject('translator')->translate( 'EasyDocs'),
                    'page'       => $page,
                    'route'      => $route,
                    'permission' => $permission,
                    'pages'      => $pages,
                    'icon'       => 'data:image/svg+xml;base64,' . base64_encode('
<svg fill="black" viewBox="-50 -50 400 400" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" 
clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414" role="img" aria-hidden="true" focusable="false">
    <path fill="black" d="M300 190.766c-.003-19.314-4.91-38.863-15.238-56.75-15.213-26.345-39.769-45.191-69.153-53.061-29.387-7.87-60.077-3.83-86.417 11.373-10.383 5.998-13.939 19.274-7.947 29.655 5.989 10.386 19.268 13.939 29.655 7.944 16.295-9.409 35.295-11.907 53.472-7.041 18.185 4.882 33.382 16.539 42.789 32.841 19.427 33.645 7.857 76.829-25.794 96.258-10.384 6.001-13.934 19.277-7.945 29.657 5.998 10.381 19.275 13.94 29.656 7.948 36.494-21.076 56.916-59.439 56.922-98.824"></path><path d="M230.226 42.998c-.003-7.383-3.765-14.572-10.559-18.645-35.48-21.251-79.752-21.618-115.544-.956-26.349 15.21-45.188 39.767-53.062 69.153-7.879 29.383-3.836 60.075 11.38 86.417 10.232 17.729 25.035 32.369 42.817 42.342 10.457 5.865 23.684 2.15 29.55-8.311 5.865-10.451 2.146-23.681-8.308-29.55-10.976-6.158-20.129-15.21-26.466-26.194-9.412-16.296-11.911-35.287-7.038-53.472 4.87-18.17 16.53-33.374 32.837-42.786 22.149-12.789 49.552-12.558 71.522.607 10.285 6.159 23.617 2.816 29.784-7.472a21.606 21.606 0 0 0 3.087-11.133"></path><path d="M227.799 178.126c-.003-.618-.006-1.228-.011-1.842-.199-11.986-10.067-21.553-22.057-21.356-11.866.188-21.358 9.871-21.358 21.699v.351c.45 25.661-13.092 49.469-35.234 62.251-16.305 9.416-35.296 11.91-53.478 7.038-18.183-4.873-33.38-16.526-42.789-32.835-6.338-10.974-9.601-23.465-9.45-36.013.004-.096.004-.188.004-.278 0-11.863-9.548-21.556-21.44-21.701-11.99-.148-21.827 9.449-21.975 21.437-.284 20.405 5.023 40.531 15.265 58.269 15.203 26.337 39.764 45.186 69.147 53.056 29.383 7.873 60.075 3.833 86.417-11.373 35.255-20.363 56.956-58.052 56.959-98.703"></path>
</svg>')
                ]
            ];
        }

        return $commands;
    }
}
