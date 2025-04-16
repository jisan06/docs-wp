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
 * Script Template Filter
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class TemplateFilterLink extends Library\TemplateFilterLink
{
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

        $text = str_replace('<ktml:link>', $links, $text);
    }
}
