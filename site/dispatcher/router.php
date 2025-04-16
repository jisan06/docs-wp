<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Component\Base;
use EasyDocLabs\EasyDoc;
use EasyDocLabs\Library;

class DispatcherRouter extends Base\DispatcherRouterAbstract
{
    protected $_routes = [];

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['resolvers' => ['site' => ['router' => $this]]]);

        parent::_initialize($config);
    }

    public function getRoute($route, array $parameters = [])
    {
        if ($route instanceof Library\ModelEntityRowset)
        {
            foreach ($route as $r)
            {
                $route = $r;
                break;
            }
        }

        if ($route instanceof EasyDoc\ModelEntityDocument || $route instanceof EasyDoc\ModelEntityCategory)
        {
            $entity = $route;

            $route = $this->findRouteBy('uuid', $route->uuid);

            if ($route) {
                $route = $route->path;
            }
        }

        if ($route === null) {
            $route = $this->getObject('request')->getUrl()->toString(Library\HttpUrl::PATH + Library\HttpUrl::QUERY + Library\HttpUrl::FRAGMENT);
        }

        $route = parent::getRoute($route, $parameters);

        if (isset($entity)) $route->getConfig()->entity = $entity;

        return $route;
    }

    public function findRouteBy($key, $value, $regenerate = false)
    {
        $query = $this->getObject('lib:database.query.select')
            ->table('easydoc_routes')
            ->where("$key = :$key")->bind(["$key" => $value]);

        $result = $this->getObject('database.driver.mysqli')->select($query, Library\Database::FETCH_OBJECT);

        $routes = $this->getRoutes();

        if (!$result || !$result->uuid)
        {
            if ($regenerate)
            {
                // Regenerating routes and try again

                try {
                    $this->getObject('com:easydoc.database.table.documents')->regenerateRoutes();
                } catch (\Exception $e) {
                    if (\Foliokit::isDebug()) $this->getObject('response')->addMessage($e->getMessage(), 'error');
                }

                $result = $this->findRouteBy($key, $value);
            }
        }
        else $routes[$result->uuid] = $result;

        return $result;
    }

    public function getRoutes()
    {
        return $this->_routes;
    }
}