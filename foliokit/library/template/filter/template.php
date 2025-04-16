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
 * Markdown Template Filter
 *
 * Filter to parse <ktml:template:[engine]></ktml:template:[engine]> tags. Content and will be rendered using the
 * specific engine.
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Template\Filter
 */
class TemplateFilterTemplate extends TemplateFilterAbstract
{
    /**
     * Replace <ktml:template:[format]></ktml:template:[format]> and rendered the content
     *
     * @param string $text  The text to parse
     * @param TemplateInterface $template A template object.
     * @return void
     */
    public function filter(&$text, TemplateInterface $template)
    {
        $factory = $this->getObject('template.engine.factory');

        $types = $factory->getFileTypes();
        $types = implode('|', $types);

        $matches = array();
        if(preg_match_all('#<ktml:template:('.$types.')>(.*)<\/ktml:template:('.$types.')>#siU', $text, $matches))
        {
            foreach($matches[0] as $key => $match)
            {
                $data = $template->getData();
                $html = $factory->createEngine($matches[1][$key], array('functions' => $template->getFunctions()))
                    ->render($matches[2][$key], $data);

                $text = str_replace($match, $html, $text);
            }
        }
    }
}
