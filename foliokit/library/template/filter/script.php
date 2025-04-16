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
 * Script Template Filter
 *
 * Filter to parse script tags
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Template\Filter
 */
class TemplateFilterScript extends TemplateFilterTag
{
    /**
     * Parse the text for script tags
     *
     * This function will selectively filter all script tags that don't have a type attribute defined or where the
     * type="text/javascript". If the element includes a data-inline attribute the element will not be excluded.
     *
     * @param string            $text The text to parse
     * @param TemplateInterface $template
     * @return string
     */
    protected function _parseTags(&$text, TemplateInterface $template)
    {
        $tags = '';

        $matches = array();
        // <ktml:script src="" />
        if(preg_match_all('#<ktml:script(?!\s+data\-inline\s*)\s+src="([^"]+)"(.*)/>#siU', $text, $matches))
        {
            foreach(array_unique($matches[1]) as $key => $match)
            {
                //Set required attributes
                $attribs = array(
                    'src' => $match
                );

                $attribs = array_merge($this->parseAttributes( $matches[2][$key]), $attribs);

                if(!isset($attribs['type'])) {
                    $attribs['type'] = 'text/javascript';
                };

                $tags .= $this->_renderTag($template, $attribs, null);
            }

            $text = str_replace($matches[0], '', $text);
        }

        $matches = array();
        // <script></script>
        if(preg_match_all('#<script(?!\s+data\-inline\s*)(.*)>(.*)</script>#siU', $text, $matches))
        {
            foreach($matches[2] as $key => $match)
            {
                $attribs = $this->parseAttributes( $matches[1][$key]);

                if(!isset($attribs['type'])) {
                    $attribs['type'] = 'text/javascript';
                };

                $tags .= $this->_renderTag($template, $attribs, $match);
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
        $link      = isset($attribs['src']) ? $attribs['src'] : false;
        $condition = isset($attribs['condition']) ? $attribs['condition'] : false;

        unset($attribs['condition']);

        if(!$link)
        {
            $script = $this->buildElement('script', $attribs, trim($content));
        }
        else
        {
            $attribs['src'] = $link;
            $script = $this->buildElement('script', $attribs);
        }

        if($condition)
        {
            $html  = '<!--[if '.$condition.']>'."\n";
            $html .= $script;
            $html .= '<![endif]-->'."\n";
        }
        else $html = $script;

        return $html."\n";
    }
}