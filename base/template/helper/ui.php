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

class TemplateHelperUi extends Base\TemplateHelperUi
{
    public function styles($config = [])
    {
        if ($dark_mode = $this->getObject('com:easydoc.model.configs')->fetch()->dark_mode) {
            $config = new Library\ObjectConfigJson($config);

            if ($dark_mode == -1) {
                $config->{'color-scheme'} = 'light';
                $config->dark_mode = false;
            } elseif ($dark_mode == 1) {
                $config->{'color-scheme'} = 'dark';
                $config->dark_mode = true;
            }
        }

        $html = parent::styles($config);

        $css = sprintf('%s/icons.css', TemplateHelperIcon::getIconsOverridePath());

        if (file_exists($css)) {
            $html .= '<ktml:style src="'.sprintf('%s/icons.css', TemplateHelperIcon::getIconsOverrideUrl()).'" />';
        }

        return $html;
    }
}