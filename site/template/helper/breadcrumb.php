<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;
use EasyDocLabs\EasyDoc;

/**
 * Breadcrumb Template Helper
 *
 * @package Koowa\Component\Files
 */
class TemplateHelperBreadcrumb extends Library\TemplateHelperAbstract
{
    public function load($config = [])
    {
        $config = new Library\ObjectConfig($config);
        
        $config->append([
            'entity' => null
        ]);

        $query         = $this->getObject('request')->query;
        $page_category = null;
        $hierarchy     = [];
        $item_count    = 2; // 1 is for root defined in template

        if ($query->page_category) {
            $page_category = $this->getObject('com:easydoc.model.categories')->uuid($query->page_category)->fetch();
        }

        if ($entity = $config->entity)
        {
            if ($entity instanceof Library\ModelEntityComposable) $entity = $entity->getIterator()->current();

            if ($entity instanceof EasyDoc\ModelEntityDocument)
            {
                $document = $entity;
                $category = $document->category;
            }
            else
            {
                $document = null;
                $category = $entity;
            }

            $skip = true;

            $anscestors = $category->getAncestors() ?? [];

            foreach ($anscestors as $breadcrumb)
            {
                if ($page_category)
                {
                    if ($breadcrumb->uuid === $page_category->uuid)
                    {
                        $skip = false;
                        continue;
                    }

                    if ($skip) {
                        continue;
                    }
                }

                $breadcrumb->breadcrumb_route = $this->getTemplate()->route($breadcrumb);
                $hierarchy[$item_count++] = $breadcrumb;
            }

            if ($entity instanceof EasyDoc\ModelEntityDocument)
            {
                $category->breadcrumb_route = $this->getTemplate()->route($category);
                $hierarchy[$item_count++] = $category;
                
                // Add document title
                $document->breadcrumb_route = false;
                $hierarchy[$item_count] = $document;
            }
            else
            {
                if (!$category->isNew() && (!$page_category || $category->uuid !== $page_category->uuid))
                {
                    $category->breadcrumb_route = false;
                    $hierarchy[$item_count] = $category;
                }
            }
        }

        return $this->getTemplate()->render('com://site/easydoc/document/breadcrumb.html', [
            'hiearchy' => $hierarchy,
            'page_category' => $page_category
        ]);
    }
}