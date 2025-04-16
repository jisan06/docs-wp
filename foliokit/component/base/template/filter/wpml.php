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

/**
 * WPML - Display HTML content during WordPress Hooks
 *
 * Syntax:
 *     `<wpml action="[action]">[content]</wpml>`
 * e.g.:
 *     `<wpml action="wp_footer"><footer><h1>Footer</h1></footer></wpml>`
 *
 * @author  Israel Canasa <https://github.com/raeldc>
 * @package EasyDocLabs\Component\Base
 */
class TemplateFilterWpml extends TemplateFilterTag
{
    public function filter(&$text, Library\TemplateInterface $template)
    {
        parent::filter($text, $template);
        $this->_renderTags();
    }

    /**
     * This use the current screen object of Wordpress to display the contents of the help tag
     * @return void
     */
    protected function _renderTags()
    {
        foreach ($this->_parsed_tags as $key => $html)
        {
            $action = $this->getObject('filter.cmd')->sanitize($html->action);

            \EasyDocLabs\WP::add_action($action, function() use($html) {
                echo $html->content;
            });
        }
    }
}
