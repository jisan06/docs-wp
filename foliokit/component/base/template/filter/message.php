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
 * Messages Template Filter
 *
 * Filter will render the response flash messages.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class TemplateFilterMessage extends Library\TemplateFilterAbstract
{
    public function filter(&$text, Library\TemplateInterface $template)
    {
        if (strpos($text, '<ktml:messages>') !== false)
        {
            $output   = '';
            $messages = $this->getObject('response')->getMessages();

            foreach ($messages as $type => $message)
            {
                if ($type === 'notice') {
                    $type = 'info';
                } elseif ($type === 'error') {
                    $type = 'danger';
                }

                $output .= '<div class="k-alert k-alert--'.strtolower($type).'">';
                $output .= implode('<br>', $message);
                $output .= '</div>';
            }

            $text = str_replace('<ktml:messages>', $output, $text);
        }
    }
}