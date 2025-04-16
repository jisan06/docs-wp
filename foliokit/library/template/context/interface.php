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
 * Template Context Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Template\Context
 */
interface TemplateContextInterface extends CommandInterface
{
    /**
     * Get the template data
     *
     * @return array
     */
    public function getData();

    /**
     * Get the template source
     *
     * @return array
     */
    public function getSource();

    /**
     * Get the template parameters
     *
     * @return array
     */
    public function getParameters();
}