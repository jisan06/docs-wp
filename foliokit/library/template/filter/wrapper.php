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
 * Wrapper Template Filter
 *
 * Filter for wrapping a template output
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Template\Filter
 */
class TemplateFilterWrapper extends TemplateFilterAbstract
{
    /**
     * @param ObjectConfig $config
     */
    protected function _initialize(ObjectConfig $config)
    {
        $config->append(array(
            'priority'  => self::PRIORITY_LOW,
        ));

        parent::_initialize($config);
    }

    /**
     * Checks if the text has <ktml:wrapper template="string with %s placeholder for template contents">
     *
     * If it does, wraps the whole output using the passed template
     *
     * @param $text
     * @param TemplateInterface $template A template object.
     * @return void
     */
    public function filter(&$text, TemplateInterface $template)
    {
        if (strpos($text, '<ktml:wrapper') !== false)
        {
            if(preg_match_all('#<ktml:wrapper\s+template="([^"]+)"\s*>#siU', $text, $matches, PREG_SET_ORDER))
            {
                foreach ($matches as $match)
                {
                    $wrapper_template = html_entity_decode($match[1]);

                    if ($wrapper_template) {
                        $text = sprintf($wrapper_template, $text);
                        $text = str_replace($matches[0], '', $text);

                        return;
                    }
                }
            }
        }
    }
}
