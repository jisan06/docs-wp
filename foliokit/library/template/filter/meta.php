<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Library;

/**
 * Meta Template Filter
 *
 * Filter to parse meta tags
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Template\Filter
 */
class TemplateFilterMeta extends TemplateFilterTag
{
    /**
     * Parse the text for script tags
     *
     * @param string            $text The text to parse
     * @param TemplateInterface $template
     * @return string
     */
    protected function _parseTags(&$text, TemplateInterface $template)
    {
        $tags = '';

        $matches = array();
        if(preg_match_all('#<meta\ content="([^"]+)"(.*)\/*>#siU', $text, $matches))
        {
            foreach($matches[1] as $key => $match)
            {
                //Set required attributes
                $attribs = array(
                    'content' => $match
                );

                $attribs = array_merge($this->parseAttributes( $matches[2][$key]), $attribs);
                $tags .= $this->_renderTag($template, $attribs, null);
            }

            $text = str_replace($matches[0], '', $text);
        }

        return $tags;
    }

    /**
     * Render the tag
     *
     * @param TemplateInterface $template
     * @param   array           $attribs Associative array of attributes
     * @param   string          $content The tag content
     * @return string
     */
    protected function _renderTag(TemplateInterface $template, $attribs = array(), $content = null)
    {
        return $this->buildElement('meta', $attribs)."\n";
    }
}