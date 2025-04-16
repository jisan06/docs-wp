<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Library;
use EasyDocLabs\WP;

/**
 * Editor Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class TemplateHelperEditor extends Library\TemplateHelperAbstract
{
    /**
     * Generates an HTML editor
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function display($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'id'    => $this->getIdentifier()->package.'-form-'.$config->name,
            'class' => $this->getIdentifier()->package.'-'.$config->name,
            'rows'  => WP::get_option('default_post_edit_rows', 10),
            'css'   => ''
        ])->append([
            'textarea_name'    => $config->name,
            'editor_class'     => $config->class,
            'textarea_rows'    => $config->rows,
            'editor_css'       => $config->css,
            'media_buttons'    => true,
            'wapautop'         => null,
            'tabindex'         => null,
            'teeny'            => false,
            'dfw'              => false,
            'tinymce'          => true,
            'quicktags'        => true,
            'drag_drop_upload' => false
        ]);

        // TODO: Implement this
        // Add editor styles and scripts in JDocument to page when rendering
        //$this->getIdentifier('com:koowa.view.page.html')->getConfig()->append(['template_filters' => ['document']]);

        WP::wp_editor($config->value, $config->id, Library\ObjectConfig::unbox($config));
    }
}
