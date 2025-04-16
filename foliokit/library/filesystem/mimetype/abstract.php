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
 * Abstract FileSystem Mimetype Resolver
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package EasyDocLabs\Library\Filesystem\Mimetype
 */
abstract class FilesystemMimetypeAbstract extends ObjectAbstract implements FilesystemMimetypeInterface
{
    /**
     * Check if the resolver is supported
     *
     * @return  boolean  True on success, false otherwise
     */
    public static function isSupported()
    {
        return true;
    }
}