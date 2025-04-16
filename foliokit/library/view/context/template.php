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
 * Template View Context
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\View\Context
 */
class ViewContextTemplate extends ViewContext
{
    /**
     * Set the view layout
     *
     * @param string $layout
     * @return ViewContextTemplate
     */
    public function setLayout($layout)
    {
        return ObjectConfig::set('layout', $layout);
    }

    /**
     * Get the view layout
     *
     * @return string
     */
    public function getLayout()
    {
        return ObjectConfig::get('layout');
    }
}