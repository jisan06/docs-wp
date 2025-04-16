<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\EasyDoc;
use EasyDocLabs\Library;

class ViewJson extends Library\ViewJson
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors' => ['pageable'],
            'fields'    => [
                'documents'  => [
                    'id',
                    'itemid',
                    'uuid',
                    'title',
                    'slug',
                    'category_slug',
                    'alias',
                    'easydoc_category_id',
                    'description',
                    'publish_date',
                    'created_on',
                    'created_by',
                    'created_by_name',
                    'modified_on',
                    'modified_by',
                    'modified_by_name',
                    'image',
                    'icon',
                    'links',
                    'storage_type',
                    'storage_path'
                ],
                'categories' => [
                    'id',
                    'itemid',
                    'uuid',
                    'title',
                    'slug',
                    'path',
                    'description',
                    'created_on',
                    'created_by',
                    'created_by_name',
                    'modified_on',
                    'modified_by',
                    'modified_by_name',
                    'image',
                    'icon',
                    'links'
                ],
                'files' => [
                    'id',
                    'name',
                    'folder',
                    'modified_on',
                    'modified_by',
                    'created_on',
                    'created_by'
                ],
                'users' => [
                    'id',
                    'name',
                    'registered_on'
                ]
            ]
        ]);

        parent::_initialize($config);
    }

    /**
     * Returns an array representing the category row
     *
     * @param Library\ModelEntityInterface $category
     *
     * @return array
     */
    protected function _getCategoryAttributes(Library\ModelEntityInterface $category)
    {
        $data = $category->toArray();

        $data['parameters'] = $category->getParameters()->toArray();
        $data['icon']       = $category->icon;
        $data['image']      = $category->image;

        return $data;
    }

    protected function _getCategoryRelationships(Library\ModelEntityInterface $category)
    {
        $this->_setCategoryData($category);
  
        $data = [
            'author' => $this->_getEntityRelationship($category->_author)
        ];

        return $data;
    }

    protected function _setDocumentData(Library\ModelEntityInterface $document)
    {
        if (!$document->_author) {
            $document->_author = $this->getObject('com:base.model.users')->id($document->created_by)->fetch();
        }

        if (!$document->storage->_links)
        {
            if (!$document->download_link) {
                $this->prepareDocument($document);
            }
    
            $link = $this->getObject('lib:http.url', ['url' => $document->download_link]);
    
            $file_link = $link->toString();
    
            $query = $link->getQuery(true);
    
            if (isset($query['format'])) unset($query['format']);
            if (isset($query['layout'])) unset($query['layout']);
    
            $link->setQuery($query);
    
            $download_link = $link->toString();
    
            $document->storage->_links = ['self' => $file_link, 'download' => $download_link];
        }
    }

    protected function _setCategoryData(Library\ModelEntityInterface $category)
    {
        $category->_author = $this->getObject('com:base.model.users')->id($category->created_by)->fetch();        
    }

    protected function _getDocumentRelationships(Library\ModelEntityInterface $document)
    {
        $this->_setDocumentData($document);

        $data = [
            'author'   => $this->_getEntityRelationship($document->_author),
            'category' => $this->_getEntityRelationship($document->category),
            'file'     => $this->_getEntityRelationship($document->storage)
        ];

        return $data;
    }

    protected function _getFileLinks(Library\ModelEntityInterface $file)
    {
        return $file->_links;
    }

    protected function _getCategoryLinks(Library\ModelEntityInterface $category)
    {
        $links = ['self' => $this->getRoute($category)]; 

        if ($category->image && $category->image_path)
        {
            $links['image'] = [
                'href' => $category->image_path
            ];
        }

        if ($category->icon && $category->icon_path)
        {
            $links['icon'] = [
                'href' => $category->icon_path
            ];
        }

        return $links;
    }

    protected function _getEntityRelationship(Library\ModelEntityInterface $entity)
    {
        return [
            'links' => $this->_callCustomMethod($entity, 'relationshipLinks'),
            'data'  => $this->_includeResource($entity, ['id', 'type'])
        ];
    }

    protected function _getDocumentRelationshipLinks(Library\ModelEntityInterface $document)
    {
        $links = $this->_getDocumentLinks($document);

        return ['self' => $links['self']];
    }

    protected function _getCategoryRelationshipLinks(Library\ModelEntityInterface $category)
    {
        $links = $this->_getCategoryLinks($category);

        return ['self' => $links['self']];

    }

    protected function _getUserRelationshipLinks(Library\ModelEntityInterface $user)
    {
        $links = parent::_getUserLinks($user);

        return ['self' => $links['self']];
    }

    protected function _getFileRelationshipLinks(Library\ModelEntityInterface $file)
    {
        $links = $this->_getFileLinks($file);

        return ['self' => $links['self']];
    }

    protected function _getFileId(Library\ModelEntityInterface $entity)
    {
        return $entity->uri;
    }

    /**
     * Returns an array representing the document row and with additional properties for download links and thumbnails
     *
     * @param Library\ModelEntityInterface $document Document row
     *
     * @return array
     */
    protected function _getDocumentAttributes(Library\ModelEntityInterface $document)
    {
        $this->prepareDocument($document);

        $data = $document->toArray();

        $data['parameters'] = $document->getParameters()->toArray();
        $data['icon']  = $document->icon;
        $data['image'] = $document->image;
     
        return $data;
    }

    protected function _getDocumentLinks(Library\ModelEntityInterface $document)
    {
        $this->_setDocumentData($document);

        $links = ['self' => $this->getRoute($document), 'download' => (string) $document->storage->_links['download']];

        if ($document->image && $document->image_path)
        {
            $links['image'] = [
                'href' => (string) $document->image_path
            ];
        }

        if ($document->icon && $document->icon_path)
        {
            $links['icon'] = [
                'href' => (string) $document->icon_path
            ];
        }

        return $links;
    }

    protected function _getEntityRoute(Library\ModelEntityInterface $entity)
    {
        if ($entity instanceof EasyDoc\ModelEntityDocument || $entity instanceof EasyDoc\ModelEntityCategory) {
            $route = $this->getRoute($entity); //$this->getObject('router')->generate($entity, ['format' => 'json']);
        } else {
            $route = parent::_getEntityRoute($entity);
        }

        return $route;
    }
}
