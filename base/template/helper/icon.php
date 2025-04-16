<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Component\Files;

class TemplateHelperIcon extends Files\TemplateHelperIcon
{
    protected static $_overrides_path = 'easydoclabs/easydoc-icons-override';

    protected static $_icons = [
        'archive'     => ['7z','gz','rar','tar','zip'],
        'audio'       => ['aif','aiff','alac','amr','flac','ogg','m3u','m4a','mid','mp3','mpa','wav','wma'],
        'document'    => ['doc','docx','rtf','txt','ppt','pptx','pps','xml'],
        'image'       => ['bmp','gif','jpg','jpeg','png','psd','tif','tiff'],
        'pdf'         => ['pdf'],
        'spreadsheet' => ['xls', 'xlsx', 'ods'],
        'video'       => ['3gp','avi','flv','mkv','mov','mp4','mpg','mpeg','rm','swf','vob','wmv'],
        'default',
        'link',
        'folder'
    ];

    public static function getIcons()
    {
        return array_keys(self::getIconExtensionMap());
    }

    public static function getIconsOverridePath()
    {
        return sprintf('%s/%s', \EasyDocLabs\WP\CONTENT_DIR, self::$_overrides_path);
    }

    public static function getIconsOverrideUrl()
    {
        return sprintf('%s/%s', \EasyDocLabs\WP\CONTENT_URL, self::$_overrides_path);
    }

    public static function getIconExtensionMap()
    {
        $config = sprintf('%s/config.php', self::getIconsOverridePath());

        if (file_exists($config)) {
            $icons = (array) include $config;
        } else {
            $icons = static::$_icons;
        }

        $map = [];

        foreach ($icons as $icon => $extensions)
        {
            if (is_numeric($icon)) {
                $map[$extensions] = [];
            } else {
                $map[$icon] = $extensions;
            }
        }

        return $map;
    }
}