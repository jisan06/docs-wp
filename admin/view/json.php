<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Library;

class ViewJson extends Library\ViewJson
{
    /**
     * Returns an array representing the category entity
     *
     * @param Library\ModelEntityInterface $category
     *
     * @return array
     */
    protected function _getCategoryAttributes(Library\ModelEntityInterface $category)
    {
        $data = $category->toArray();
        $data['parameters'] = $category->getParameters()->toArray();
        $data['access_title'] = $category->access_title;
        $data['icon']  = $category->icon;
        $data['image'] = $category->image;

        return $data;
    }

    protected function _getCategoryLinks(Library\ModelEntityInterface $category)
    {
        //$links = ['self' => (string)$this->_getEntityRoute($category)];
        $links = ['self' => $this->getRoute($category, ['format' => 'json', 'view' => 'category'])];

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

    /**
     * Returns an array representing the document entity
     *
     * @param Library\ModelEntityInterface $document Document row
     *
     * @return array
     */
    protected function _getDocumentAttributes(Library\ModelEntityInterface $document)
    {
        $data = $document->toArray();

        $data['parameters'] = $document->getParameters()->toArray();
        $data['access_title'] = $document->access_title;
        $data['icon']  = $document->icon;
        $data['image'] = $document->image;

        $data['category'] = [
            'id'    => $document->easydoc_category_id,
            'title' => $document->category_title,
            'slug'  => $document->category_slug
        ];

        $data['file'] = [];

        if ($document->storage_type === 'file')
        {
            if ($document->mimetype) {
                $data['file']['type'] = $document->mimetype;
            }

            if ($document->extension) {
                $data['file']['extension'] = $document->extension;
            }

            if ($document->size) {
                $data['file']['size'] = $document->size;
            }
        }

        return $data;
    }

    protected function _getDocumentLinks(Library\ModelEntityInterface $document)
    {
        //$links = ['self' => (string)$this->_getEntityRoute($document)];
        $links = ['self' => $this->getRoute($document, ['format' => 'json'])];


        if ($document->storage_type != 'remote')
        {
            $folder = $document->storage->folder === '.' ? '' : rawurlencode($document->storage->folder);
           
            $file_link = $this->getRoute(sprintf('view=file&routed=1&container=easydoc-files&folder=%s&name=%s', $folder,rawurlencode($document->storage->name)));
        }
        else $file_link = $document->storage_path;


        $links['file'] = [
            'href' => $file_link,
            'type' => $this->mimetype
        ];

        $category_link = $this->getRoute($document->category, ['format' => 'json', 'view' => 'category']);
             
        $links['category'] = [
            'href' => (string) $category_link,
            'type' => $this->mimetype
        ];

        if ($document->image && $document->image_path)
        {
            $links['image'] = [
                'href' => $document->image_path
            ];
        }

        if ($document->icon && $document->icon_path)
        {
            $links['icon'] = [
                'href' => $document->icon_path
            ];
        }

        return $links;
    }
}
