<?php
/**
 * FolioKit Scheduler
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Scheduler;
use EasyDocLabs\Library;
use EasyDocLabs\Component\Base;

class Version extends Library\ObjectAbstract
{
    const VERSION = '1.3.2';

    /**
     * Get the version
     *
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }
}
