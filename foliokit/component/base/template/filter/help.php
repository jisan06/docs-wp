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
use EasyDocLabs\WP;

/**
 * Title Template Filter
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class TemplateFilterHelp extends TemplateFilterTag
{
    public function filter(&$text, Library\TemplateInterface $template)
    {
        // Render the contents of the help tags during this hook.
        WP::add_action('current_screen', [$this, 'renderHelp']);

        parent::filter($text, $template);
    }

    /**
     * This use the current screen object of Wordpress to display the contents of the help tag
     * @return void
     */
    public function renderHelp()
    {
        $screen     = WP::get_current_screen();

        foreach ($this->_parsed_tags as $key => $help)
        {
            $id = $this->getObject('filter.slug')->sanitize($help->title);

            $screen->add_help_tab([
                'id'      => $id,
                'title'   => $help->title,
                'content' => $help->content
            ]);
        }
    }
}
