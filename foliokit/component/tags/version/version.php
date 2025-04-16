<?php
/**
 * FolioKit Tags
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Tags;

use EasyDocLabs\Library;

/**
 * Version
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Tags
 */
class Version extends Library\ObjectAbstract
{
    const VERSION = '1.2.1';

    /**
     * Get the version.
     *
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }
}