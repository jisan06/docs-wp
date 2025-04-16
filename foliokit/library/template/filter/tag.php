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
 * Abstract Tag Template Filter
 *
 * Filter to parse tags
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Template\Filter
 */
abstract class TemplateFilterTag extends TemplateFilterAbstract
{
    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param  ObjectConfig $config  An optional ObjectConfig object with configuration options
     * @return void
     */
    protected function _initialize(ObjectConfig $config)
    {
        $config->append(array(
            'priority' => self::PRIORITY_LOW,
        ));

        parent::_initialize($config);
    }

    /**
     * Find any virtual tags and render them
     *
     * This function will pre-pend the tags to the content
     *
     * @param string $text  The text to parse
     * @param TemplateInterface $template A template object.
     * @return void
     */
    public function filter(&$text, TemplateInterface $template)
    {
        //Parse the tags
        $tags = $this->_parseTags($text, $template);

        //Prepend the tags again to the text
        $text = $tags.$text;
    }

    /**
     * Parse the text for the tags
     *
     * @param string            $text The text to parse
     * @param TemplateInterface $template
     * @return string
     */
    abstract protected function _parseTags(&$text, TemplateInterface $template);

    /**
     * Render the tag
     *
     * @param TemplateInterface $template
     * @param   array           $attribs Associative array of attributes
     * @param   string          $content The element content
     * @return string
     */
    abstract protected function _renderTag(TemplateInterface $template, $attribs = array(), $content = null);
}