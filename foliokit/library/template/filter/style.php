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
 * Style Template Filter
 *
 * Filter to parse style tags
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Template\Filter
 */
class TemplateFilterStyle extends TemplateFilterTag
{
    /**
     * Parse the text for style tags
     *
     * @param string            $text The text to parse
     * @param TemplateInterface $template
     * @return string
     */
    protected function _parseTags(&$text, TemplateInterface $template)
    {
        $tags = '';

        $matches = array();
        if(preg_match_all('#<ktml:style\s+src="([^"]+)"(.*)\/>#siU', $text, $matches))
        {
            foreach(array_unique($matches[1]) as $key => $match)
            {
                //Set required attributes
                $attribs = array(
                    'src' => $match
                );

                $attribs = array_merge($this->parseAttributes( $matches[2][$key]), $attribs);
                $tags .= $this->_renderTag($template, $attribs, null);
            }

            $text = str_replace($matches[0], '', $text);
        }

        $matches = array();
        if(preg_match_all('#<style(.*)>(.*)<\/style>#siU', $text, $matches))
        {
            foreach($matches[2] as $key => $match)
            {
                $attribs = $this->parseAttributes( $matches[1][$key]);
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

        if(!$link)
        {
            $style = $this->buildElement('style', $attribs, trim($content));
        }
        else
        {
            $attribs['rel'] = 'stylesheet';
            $attribs['href'] = $link;

            unset($attribs['src']);

            $style = $this->buildElement('link', $attribs);
        }

        if($condition)
        {
            $html  = '<!--[if '.$condition.']>'."\n";
            $html .=  $style;
            $html .= '<![endif]-->'."\n";
        }
        else $html = $style;

        return $html."\n";
    }
}
