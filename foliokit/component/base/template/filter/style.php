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
 * Style Template Filter
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class TemplateFilterStyle extends Library\TemplateFilterStyle implements Library\ObjectSingleton
{
    /**
     * An array of namespaces for loaded inline styles
     *
     * @var array
     */
    protected $_loaded_styles = [];

    /**
     * String of Inline Styles
     *
     * @var string
     */
    protected $_inline_styles = '';

    /**
     * A map from keys to rendered link tags
     *
     * @var array
     */
    protected $_link_tags = [];

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
        $styles = $this->_parseTags($text, $template);

        if($template->decorator() !== 'wordpress') {
            $text = str_replace('<ktml:style>', $styles, $text);
        }
        else {
            static $special_loader_added = false;

            if (!$special_loader_added) {
                $special_loader_added = true;

                WP::add_filter('style_loader_tag', function($tag, $handle, $src) {
                    if (array_key_exists($handle, $this->_link_tags)) {
                        return $this->_link_tags[$handle];
                    }

                    return $tag;
                }, 100000, 3);
            }

            $text = $styles.$text;

            $action = WP::is_admin() ? 'admin_print_styles' : 'wp_print_styles';

            WP::add_action($action, function() { echo $this->_inline_styles; }, 100);
            $text = str_replace('<ktml:style>', '', $text);
        }
    }

    /**
     * Render the tag
     *
     * @param TemplateInterface $template
     * @param   array           $attribs Associative array of attributes
     * @param   string          $content The tag content
     * @return string
     */
    protected function _renderTag(Library\TemplateInterface $template, $attribs = array(), $content = null)
    {
        if($template->decorator() === 'wordpress')
        {
            $link = isset($attribs['src']) ? $attribs['src'] : false;

            if (!$link)
            {
                $hash = md5($content.serialize($attribs));

                if (!isset($this->_loaded_styles[$hash]))
                {
                    $this->_inline_styles .= parent::_renderTag($template, $attribs, $content);

                    $this->_loaded_styles[$hash] = true;
                }
            }
            else
            {
                $media = isset($attribs['media']) ? $attribs['media']   : 'all';

                $handle = 'easydoc-'.md5($link);
                $this->_link_tags[$handle] = parent::_renderTag($template, $attribs, $content);

                WP::wp_enqueue_style($handle, $link, [], null, $media);
            }

            return '';
        }
        else return parent::_renderTag($template, $attribs, $content);
    }
}
