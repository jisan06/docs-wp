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
 * Title Template Filter
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class TemplateFilterTitle extends Library\TemplateFilterTitle
{
    /**
     * Current Title
     *
     * @var string
     */
    protected $_title;

    /**
     * Separator String of Concatenation
     *
     * @var string
     */
    protected $_separator;

    /**
     * Append or Prepend
     *
     * @var string
     */
    protected $_concatenation;

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
        $links   = $this->_parseTags($text, $template);

        if($template->decorator() !== 'wordpress') {
            $text = str_replace('<ktml:title>', $links, $text);
        } elseif (!empty($links))  {
            $text = $links.$text;

            $action = WP::is_admin() ? 'admin_title' : 'wp_title';

            WP::add_action($action, function($title) use ($links) {
                return strip_tags($links);
            });

        }
    }

}
