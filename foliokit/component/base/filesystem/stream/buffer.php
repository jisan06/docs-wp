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
 * Buffer FileSystem Stream
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class FilesystemStreamBuffer extends Library\FilesystemStreamBuffer
{
    /**
     * Returns a directory path for temporary files
     *
     * Use get_temp_dir() to find the Wordpress directory for temporary files
     *
     * @throws \RuntimeException If a temporary writable directory cannot be found
     * @return string Folder path
     */
    public function getTemporaryDirectory()
    {
        return \EasyDocLabs\WP::get_temp_dir();
    }
}