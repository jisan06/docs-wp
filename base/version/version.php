<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class Version extends Library\ObjectAbstract
{
    const VERSION = '1.2.5';

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
