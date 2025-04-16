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
 * Include Template Filter
 *
 * Filter to parse include tags
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Template\Filter
 */
class TemplateFilterInclude extends TemplateFilterTag
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
        $matches = array();
        $results = array();
        if(preg_match_all('#<ktml:include\s+src="([^"]+)"(.*)>#siU', $text, $matches))
        {
            foreach(array_unique($matches[1]) as $key => $match)
            {
                //Set required attributes
                $attribs = array(
                    'src' => $match
                );

                $attribs   = array_merge($this->parseAttributes( $matches[2][$key]), $attribs);
                $results[] = $this->_renderTag($template, $attribs, null);
            }

            $text = str_replace($matches[0], $results, $text);
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
    protected function _renderTag(TemplateInterface $template, $attribs = array(), $content = null)
    {
        $result = '';
        $link   = isset($attribs['src']) ? $attribs['src'] : false;

        if($link)
        {
            $url        = $this->getObject('lib:http.url', array('url' => $link));
            $identifier = $this->getIdentifier($url->toString(HttpUrl::BASE));

            //Include the component
            $result = $this->getObject('com:'.$identifier->package.'.dispatcher.fragment')
                ->include($url);

            if($this->getObject('template.engine.factory')->isDebug())
            {
                $format  = PHP_EOL.'<!--BEGIN ktml:include '.$url.' -->'.PHP_EOL;
                $format .= '%s';
                $format .= PHP_EOL.'<!--END ktml:include '.$url.' -->'.PHP_EOL;

                $result = sprintf($format, trim($result));
            }
        }

        return $result;
    }
}