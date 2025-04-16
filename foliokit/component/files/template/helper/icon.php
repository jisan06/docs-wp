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

class TemplateHelperIcon extends Library\TemplateHelperAbstract
{
    public static $icon_extension_map = [
        'archive'     => ['7z','gz','rar','tar','zip'],
        'audio'       => ['aif','aiff','alac','amr','flac','ogg','m3u','m4a','mid','mp3','mpa','wav','wma'],
        'document'    => ['doc','docx','rtf','txt','ppt','pptx','pps','xml'],
        'image'       => ['bmp','gif','jpg','jpeg','png','psd','tif','tiff'],
        'pdf'         => ['pdf'],
        'spreadsheet' => ['xls', 'xlsx', 'ods'],
        'video'       => ['3gp','avi','flv','mkv','mov','mp4','mpg','mpeg','rm','swf','vob','wmv']
    ];

    public static function getIconExtensionMap()
    {
        return self::$icon_extension_map;
    }

    public function icon_map($config = [])
    {
        $icon_map = json_encode(static::getIconExtensionMap());

        $html = "
            <script>
            if (typeof Files === 'undefined') Files = {};

            Files.icon_map = $icon_map;
            </script>";

        return $html;
    }

    /**
     * Gets the icon for the given extension
     *
     * @param array|Library\ObjectConfig $config
     * @return string Icon class, "default" if the extension doesn't exist in the map
     */
    public function icon($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'extension' => null
        ]);

        $icon = 'default';

        if ($config->extension)
        {
            $extension = strtolower($config->extension);

            foreach (static::getIconExtensionMap() as $type => $extensions)
            {
                if (in_array($extension, $extensions))
                {
                    $icon = $type;
                    break;
                }
            }
        }

        return $icon;
    }
}