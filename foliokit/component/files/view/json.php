<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Files;

use EasyDocLabs\Library;

/**
 * Json View
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ViewJson extends Library\ViewJson
{
    protected function _fetchData(Library\ViewContext $context)
    {
        $model  = $this->getModel();
        $data = [];

        foreach ($model->fetch() as $entity) {
            $data[] = $this->_getEntity($entity);
        }

        $output = [
            'version' => $this->_version,
            'links' => [
                'self' => [
                    'href' => (string) $this->_getPageUrl(),
                    'type' => 'application/json; version=1.0',
                ]
            ],
            'meta'     => [],
            'entities' => $data,
            'linked'   => []
        ];

        if ($this->isCollection())
        {
            $total  = $model->count();
            $limit  = (int) $model->getState()->limit;
            $offset = (int) $model->getState()->offset;

            $output['meta'] = [
                'offset'   => $offset,
                'limit'    => $limit,
                'total'	   => $total
            ];

            if ($limit && $total-($limit + $offset) > 0)
            {
                $output['links']['next'] = [
                    'href' => $this->_getPageUrl(['offset' => $limit+$offset]),
                    'type' => 'application/json; version=1.0',
                ];
            }

            if ($limit && $offset && $offset >= $limit)
            {
                $output['links']['previous'] = [
                    'href' => $this->_getPageUrl(['offset' => max($offset-$limit, 0)]),
                    'type' => 'application/json; version=1.0',
                ];
            }
        }

        $context->content = $output;

        if (!$this->isCollection())
        {
            $entity = $this->getModel()->fetch();
            $status = $entity->getStatus() !== Library\Database::STATUS_FAILED;

            $context->content['status'] = $status;

            if ($status === false){
                $context->content['error'] = $entity->getStatusMessage();
            }
        }
    }

    /**
     * Get the item data
     *
     * @param Library\ModelEntityInterface  $entity   Document row
     * @return array The array with data to be encoded to json
     */
    protected function _getEntity(Library\ModelEntityInterface $entity)
    {
        $method = '_get'.ucfirst($entity->getIdentifier()->name);

        if ($method !== '_getEntity' && method_exists($this, $method)) {
            $data = $this->$method($entity);
        } else {
            $data = $entity->toArray();
        }

        if (!empty($this->_fields)) {
            $data = array_intersect_key($data, array_merge(['links' => 'links'], array_flip($this->_fields)));
        }

        if (!isset($data['links'])) {
            $data['links'] = [];
        }

        if (!isset($data['links']['self']))
        {
            $data['links']['self'] = [
                'href' => (string) $this->_getEntityRoute($entity),
                'type' => 'application/json; version=1.0',
            ];
        }

        return $data;
    }

    protected function _convertRelativeLinks(Library\ViewContextInterface $context)
    {
    }

    /**
     * Get the page link
     *
     * @param  array  $query Additional query parameters to merge
     * @return string
     */
    protected function _getPageUrl(array $query = [])
    {
        $url = $this->getUrl();

        if ($query) {
            $url->setQuery(array_merge($url->getQuery(true), $query));
        }

        return (string) $url;
    }
}
