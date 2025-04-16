<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelEntityCategory extends Library\ModelEntityRow
{
    protected static $_image_path = null;

    public function save()
    {
        if (!$this->getParameters()->icon) {
            $this->getParameters()->icon = 'folder';
        }

        return parent::save();
    }

    public function toArray()
    {
        $data = parent::toArray();
        $data['hierarchy_title']  = $this->hierarchy_title;
        $data['parent_id']        = $this->getParentId();
        $data['automatic_folder'] = $this->automatic_folder;
        $data['image_path'] = $this->image_path;

        return $data;
    }

    public function getPropertyHierarchyTitle()
    {
        return str_repeat('- ', ($this->level - 1) >= 0 ? ($this->level - 1) : 0) . $this->title;
    }

    public function getPropertyImagePath()
    {
        if ($this->image)
        {
            if (static::$_image_path === null) {
                $container = $this->getObject('com:files.model.containers')->slug('easydoc-images')->fetch();

                static::$_image_path = $this->getObject('request')->getSiteUrl().'/'.$container->path;
            }

            $image = implode('/', array_map('rawurlencode', explode('/', $this->image)));

            return static::$_image_path.'/'.$image;
        }

        return null;

    }

    public function getPropertyIcon()
    {
        $icon = $this->getParameters()->get('icon', 'folder');

        // Backwards compatibility: remove .png from old style icons
        if (substr($icon, 0, 5) !== 'icon:' && substr($icon, -4) === '.png') {
            $icon = substr($icon, 0, strlen($icon)-4);
        }

        return $icon;
    }

    public function getPropertyIconPath()
    {
        $path = $this->icon;

        if (substr($path, 0, 5) === 'icon:')
        {
            if (static::$_icon_path === null) {
                $container = $this->getObject('com:files.model.containers')->slug('easydoc-icons')->fetch();

                static::$_icon_path = $this->getObject('request')->getSiteUrl().'/'.$container->path;
            }

            $icon = implode('/', array_map('rawurlencode', explode('/', substr($path, 5))));
            $path = static::$_icon_path.'/'.$icon;
        } else {
            $path = null;
        }

        return $path;
    }

    public function getPropertyDescriptionSummary()
    {
        $description = $this->description;

        if ($description) 
        {
            $position    = strpos($description, '<!--more-->');
            if ($position !== false) {
                return substr($description, 0, $position);
            }
        }
        
        return $description;
    }

    public function getPropertyDescriptionFull()
    {
        return str_replace('<!--more-->', '', $this->description ?: '');
    }
}
