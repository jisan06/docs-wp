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
 * Debug Template Helper
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Base
 */
class TemplateHelperDebug extends Library\TemplateHelperDebug
{
    /**
     * Removes WordPress root from a filename replacing them with the plain text equivalents.
     *
     * @param 	array 	$config An optional array with configuration options
     * @return	string	Html
     */
    public function path($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'root'  => \EasyDocLabs\WP\ABSPATH,
        ]);

        return parent::path($config);
    }
}