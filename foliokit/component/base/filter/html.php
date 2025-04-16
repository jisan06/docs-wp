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
 * Html Filter
 *
 * Forked from the php input filter library by: Daniel Morris <dan@rootcube.com>
 * Original Contributors: Gianpaolo Racca, Ghislain Picard, Marco Wandschneider,
 * Chris Tobin.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Filter
 */
class FilterHtml extends Library\FilterHtml
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['allowed_tags' => [
            'a'          => [
                'class' => [],
                'href'  => [],
                'rel'   => [],
                'title' => []
            ],
            'abbr'       => [
                'title' => []
            ],
            'b'          => [],
            'blockquote' => [
                'cite' => []
            ],
            'cite'       => [
                'title' => []
            ],
            'code'       => [],
            'del'        => [
                'datetime' => [],
                'title'    => []
            ],
            'dd'         => [],
            'div'        => [
                'class' => [],
                'title' => [],
                'style' => []
            ],
            'dl'         => [],
            'dt'         => [],
            'em'         => [],
            'h1'         => [],
            'h2'         => [],
            'h3'         => [],
            'h4'         => [],
            'h5'         => [],
            'h6'         => [],
            'i'          => [],
            'img'        => [
                'alt'    => [],
                'class'  => [],
                'height' => [],
                'src'    => [],
                'width'  => []
            ],
            'li'         => [
                'class' => []
            ],
            'ol'         => [
                'class' => []
            ],
            'p'          => [
                'class' => [],
                'style' => []
            ],
            'q'          => [
                'cite'  => [],
                'title' => []
            ],
            'span'       => [
                'class' => [],
                'title' => [],
                'style' => []
            ],
            'strike'     => [],
            'strong'     => [],
            'ul'         => [
                'class' => []
            ],
            'pre'        => []
        ],
            'filter_name' => 'easydoc_html_allowed_tags'
        ]);
    }

    public function sanitize($value)
    {
        $config = $this->getConfig();

        $allowed_tags = WP::apply_filters($config->filter_name, $config->allowed_tags);

        return wp_kses($value, Library\ObjectConfig::unbox($allowed_tags));
    }
}