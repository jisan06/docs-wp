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
 * Title Template Filter
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
abstract class TemplateFilterTag extends Library\TemplateFilterAbstract
{
    /**
     * Contains an array of contents from the parsed tags
     * @var array
     */
    protected $_parsed_tags = [];

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   Library\ObjectConfig $config Configuration options
     * @return  void
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'priority' => self::PRIORITY_LOW,
            'tag'      => $this->getIdentifier()->name
        ]);

        parent::_initialize($config);
    }

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
        //Parse the tags
        $tags = $this->_parseTags($text, $template);

        //Prepend the tags again to the text
        $text = $tags.$text;
    }

    /**
     * Parse the text for html tags
     *
     * @param string                    $text The text to parse
     * @param Library\TemplateInterface $template
     * @return array
     */
    protected function _parseTags(&$text, Library\TemplateInterface $template)
    {
        $tag = $this->getConfig()->tag;

        if (!empty($tag))
        {
            $matches = [];
            if(preg_match_all('#<'.$tag.'(.*)>(.*)<\/'.$tag.'>#siU', $text, $matches))
            {
                foreach($matches[2] as $key => $match)
                {
                    $attribs = new Library\ObjectConfig(array_merge(['content' => $match], $this->parseAttributes($matches[1][$key])));
                    $this->_parsed_tags[] = $attribs;
                }

                $text = str_replace($matches[0], '', $text);
            }
        }
    }
}
