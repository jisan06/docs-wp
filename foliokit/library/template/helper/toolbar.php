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
 * Toolbar Template Helper
 *
 * Extended by each specific toolbar renderer
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Template\Helper
 */
abstract class TemplateHelperToolbar extends TemplateHelperAbstract
{
    /**
     * Returns the type of toolbar this helper can render
     *
     * @return string
     */
    public function getToolbarType()
    {
        return $this->getIdentifier()->getName();
    }
}