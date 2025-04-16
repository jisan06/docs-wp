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
 * Menu bar Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class TemplateHelperMenubar extends Library\TemplateHelperToolbar
{
    /**
     * Render the menu bar
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function render($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'toolbar' => null
        ]);

        $html = '<ul class="k-navigation">';

        $router = $this->getObject('router');

        foreach ($config->toolbar->getCommands() as $command)
        {
            $command->append([
                'attribs' => [
                    'href' => '#',
                    'class' => []
                ]
            ]);

            if(!empty($command->href)) {
                parse_str($command->href, $query);
                $command->attribs['href'] = $router->generate($this->getIdentifier()->getPackage().':', $query);
            }

            $url = Library\HttpUrl::fromString($command->attribs->href);

            if (isset($url->query['view'])) {
                $command->attribs->class->append('k-navigation-'.$url->query['view']);
            }

            $attribs = clone $command->attribs;
            $attribs->class = implode(" ", Library\ObjectConfig::unbox($attribs->class));

            $html .= '<li'.($command->active ? ' class="k-is-active"' : '').'>';
            $html .= '<a '.$this->buildAttributes($attribs).'>';
            $html .= $this->getObject('translator')->translate($command->label);
            $html .= '</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }
}
