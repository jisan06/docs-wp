<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

class TemplateHelperString extends Base\TemplateHelperBehavior
{
    public function humanize($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'string' => '',
            'strip_extension' => false
        ]);

        $string = $config->string;

        if ($config->strip_extension) {
            $string = \Foliokit\pathinfo($string, PATHINFO_FILENAME);
        }

        $string = str_replace(['_', '-', '.'], ' ', $string);
        $string = ucfirst($string);

        return $string;
    }

    public function truncate($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
                'text' => '',
                'offset' => 0,
                'length' => 100,
                'pad' => '...']
        );

        // Don't show endstring if actual string length is less than cutting length
        $config->pad = (mb_strlen($config->text ?: '') < $config->length) ? '' : $config->pad;

        return mb_substr(\EasyDocLabs\WP::wp_strip_all_tags($config->text ?: ''), $config->offset, $config->length) . $config->pad;
    }

    /**
     * Converts a byte size to human readable format e.g. 1 Megabyte for 1048576
     *
     * @param array $config
     */
    public function humanize_filesize($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        return $this->getObject('com:files.template.helper.filesize')->humanize([
            'size' => $config->size
        ]);
    }
}
