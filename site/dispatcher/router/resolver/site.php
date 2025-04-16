<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/nooku/nooku-framework-wordpress for the canonical source repository
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

class DispatcherRouterResolverSite extends Base\DispatcherRouterResolverSite
{
    protected $_router;

    const INTERNAL_PREFIX = 'route:';

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_router = $config->router;
    }

    public function resolve($route, array $parameters = array())
    {
        $resolved = true;

        $router = $this->_getRouter();

        $path       = $route->path;
        $segments   = is_string($path) ? (!empty($path) ? explode('/', $path) : array()) : $path;

        $is_form     = isset($parameters['layout']) && $parameters['layout'] === 'form';
        $is_download = isset($parameters['layout']) && $parameters['layout'] === 'file';

        if (count($segments))
        {
            if ($segments[0] === self::INTERNAL_PREFIX)
            {
                array_shift($segments);

                $template = ['view', 'id'];

                foreach ($segments as $key => $segment)
                {
                    if (!isset($parameters[$template[$key]])) {
                        $parameters[$template[$key]] = $segment;
                    }
                }
            }
            elseif ($r = $router->findRouteBy('path', $this->_getAbsoluteEntityPath(implode('/', $segments), $parameters)))
            {
                $parameters['uuid'] = $r->uuid;

                 if ($r->type === 'document')
                 {
                    if (!$is_download)
                    {
                        $parameters['view']   = 'document';
                        $parameters['layout'] = 'default';
                    }
                    else $parameters['view'] = 'download';
                 }
                 elseif ($is_form) $parameters['view'] = 'category';
            }
            else $resolved = false;
        }

        if (!isset($parameters['view'])) {
            $parameters['view'] = 'list';
        }

        $route->setQuery($parameters);

        if ($resolved) $route->setResolved();

        return parent::resolve($route, $parameters);
    }

    public function generate($route, array $parameters = [])
    {
        $router = $this->_getRouter();

        $segments = array();

        unset($parameters['component']);

        if ($route instanceof Library\ModelEntityRowset && count($route) === 1) {
            $route = $route->getIterator()->current();
        }

        $entity = $route->getConfig()->entity ?? false;

        $isFormLink = (isset($parameters['layout']) && $parameters['layout'] === 'form');
        $isDocumentEntity = $entity && $entity->getIdentifier()->getName() == 'document';
        $isCategoryEntity = $entity && $entity->getIdentifier()->getName() == 'category';
        $isEntityLink = isset($parameters['uuid']) || $isDocumentEntity || $isCategoryEntity;

        if ($isFormLink && $isEntityLink)
        {
            $parameters['endpoint'] = '~documents';

            // This ensures DispatcherPage::dispatch will render the page itself without waiting for Wordpress page rendering
            if (!get_option('permalink_structure')) {
                $parameters['component'] = 'easydoc';
            }

            if ($isDocumentEntity || $isCategoryEntity) {
                $parameters['uuid'] = $entity->uuid;
                $parameters['view'] = $isDocumentEntity ? 'document' : 'category';
            } else {
                $r = $router->findRouteBy('uuid', $parameters['uuid'], true);
                $parameters['view'] = $r->type;
            }
        }
        else if (isset($parameters['uuid']))
        {
            $routes = $this->_getRoutes();

            if (!isset($routes[$parameters['uuid']]))
            {
                $r = $router->findRouteBy('uuid', $parameters['uuid']);

                if ($r && $r->path) {
                    $segments[] = $this->_getRelativeEntityPath($r->path);
                }
            }
            else $segments[] = $this->_getRelativeEntityPath($routes[$parameters['uuid']]->path);

            unset($parameters['view']);
            unset($parameters['uuid']);
        }
        elseif ($isEntityLink)
        {
            $path = trim($route->getPath(), '/');

            if (!isset($parameters['endpoint'])) $path = $this->_getRelativeEntityPath($path);

            $segments[] = $path;

            unset($parameters['view']);
        }
        else
        {
            $parameters['endpoint'] = '~documents';

            $segments[] = self::INTERNAL_PREFIX;

            if (isset($parameters['view']))
            {
                $segments[] = $parameters['view'];
                unset($parameters['view']);
            }

            if (isset($parameters['id']) && is_scalar($parameters['id']))
            {
                $segments[] = $parameters['id'];
                unset($parameters['id']);
            }

            // This ensures DispatcherPage::dispatch will render the page itself without waiting for Wordpress page rendering
            if (!get_option('permalink_structure')) {
                $parameters['component'] = 'easydoc';
            }
        }

        // Link directly to page
        if (isset($parameters['root'])) {
            $segments = [];
            $parameters = [];
        }

        if (!empty($parameters['internal']) || $isFormLink) {
            $parameters['endpoint'] = '~documents';

            unset($parameters['internal']);
        }

        $request = $this->getObject('request');

        if (isset($parameters['layout']) && $parameters['layout'] == 'form' && $request->getQuery()->layout == 'form')
        {
            if (isset($request->getReferrer()->query['referrer'])) {
                $referrer = $request->getReferrer()->query['referrer'];
            } else {
                $referrer = base64_encode($request->getReferrer()->toString());
            }

            $parameters['referrer'] = $referrer;
        }

        $route->setQuery($parameters);
        $route->setPath(implode('/', $segments));

        return parent::generate($route, $parameters);
    }

    /**
     * Returns the entity path relative to the current page.
     *
     * For example, if the current entity has the path foo/bar/baz/bat and the current page has the category
     * set to foo/bar the function will return baz/bat
     *
     * @param $path string Entity path
     * @return string Relative path
     */
    protected function _getRelativeEntityPath($path)
    {
        $query = $this->getObject('request')->getQuery();

        if ($query->page_category)
        {
            $page_route = $this->_getRouter()->findRouteBy('uuid', $query->page_category);

            if ($page_route)
            {
                $route = $page_route->path;

                if (strpos($path, $route) === 0) {
                    $path = substr($path, strlen($route)+1);
                }
            }
        }

        return $path;
    }

    /**
     * Returns the absolute entity path taking the current page category into account.
     *
     * For example, if the URL path is baz/bat and the current page has the category set to foo/bar
     * the function will return foo/bar/baz/bat
     *
     * @param $path string URL path
     * @return string Absolute path
     */
    protected function _getAbsoluteEntityPath($path, $parameters = [])
    {
        $query = $this->getObject('request')->getQuery();

        $page_category = $parameters['page_category'] ?? $query->page_category;

        if ($page_category)
        {
            $page_route = $this->_getRouter()->findRouteBy('uuid', $page_category);

            if ($page_route) {
                $path = $page_route->path.'/'.$path;
            }
        }

        return $path;
    }

    protected function _getRouter()
    {
        return $this->_router;
    }

    protected function _getRoutes()
    {
        return $this->_getRouter()->getRoutes();
    }
}