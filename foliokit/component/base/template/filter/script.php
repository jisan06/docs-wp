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
 * Script Template Filter
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class TemplateFilterScript extends Library\TemplateFilterScript implements Library\ObjectSingleton
{
    /**
     * The footer scripts
     *
     * @var string
     */
    protected $_footer_scripts = '';

    /**
     * The header scripts
     *
     * @var string
     */
    protected $_header_scripts = '';

    /**
     * A map from keys to rendered script tags
     *
     * @var array
     */
    protected $_script_tags = [];

    /**
     * Find any virtual tags and render them
     *
     * This function will pre-pend the tags to the content
     *
     * @param string                    $text The text to parse
     * @param Library\TemplateInterface $template
     */
    public function filter(&$text, Library\TemplateInterface $template)
    {
        $scripts = $this->_parseTags($text, $template);

        if($template->decorator() !== 'wordpress') {
            $text = str_replace('<ktml:script>', $scripts, $text);
        }
        else {
            static $special_loader_added = false;

            if (!$special_loader_added) {
                $special_loader_added = true;

                WP::add_filter('script_loader_tag', function($tag, $handle, $src) {
                    if (array_key_exists($handle, $this->_script_tags)) {
                        return $this->_script_tags[$handle];
                    }

                    return $tag;
                }, 100000, 3);
            }

            $text = $scripts.$text;

            $action = WP::is_admin() ? 'admin_print_footer_scripts' : 'wp_print_footer_scripts';

            WP::add_action($action, function() { echo $this->_header_scripts; }, 100);
            WP::add_action($action, function() { echo $this->_footer_scripts; }, 100);
        }
    }

    /**
     * Render the tag
     *
     * @param Library\TemplateInterface $template
     * @param   array           $attribs Associative array of attributes
     * @param   string          $content The tag content
     * @return string
     */
    protected function _renderTag(Library\TemplateInterface $template, $attribs = array(), $content = null)
    {
        if($template->decorator() === 'wordpress')
        {
            $location = !isset($attribs['location']) || $attribs['location'] !== 'footer' ? 'header': 'footer';
            $link     = isset($attribs['src']) ? $attribs['src'] : false;

            unset($attribs['location']);

            if(!$link)
            {
                $attributes = $this->buildAttributes($attribs);

                $html  = '<script '.$attributes.'>'."\n";
                $html .= trim($content);
                $html .= '</script>'."\n";

                if ($location === 'footer') {
                    $this->_footer_scripts .= $html;
                } else {
                    $this->_header_scripts .= $html;
                }
            } else {
                $handle = 'easydoc-'.md5($link);
                $this->_script_tags[$handle] = parent::_renderTag($template, $attribs, $content);

                WP::wp_enqueue_script($handle, $link, [], null, $location);
            }

            return '';
        }
        else return parent::_renderTag($template, $attribs, $content);
    }
}
