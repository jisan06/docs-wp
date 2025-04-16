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
 * FileSystem Mimetype Resolver Interface
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package EasyDocLabs\Library\Filesystem\Mimetype
 */
interface FilesystemMimetypeInterface
{
    /**
     * Find the mime type of the file with the given path.
     *
     * @param string $path The path to the file
     * @return string The mime type or NULL, if none could be guessed
     */
    public function fromPath($path);

    /**
     * Find the mime type of the given stream
     *
     * @param FilesystemStreamInterface $stream
     * @return string The mime type or NULL, if none could be guessed
     */
    public function fromStream(FilesystemStreamInterface $stream);

    /**
     * Check if the finder is supported
     *
     * @return  boolean  True on success, false otherwise
     */
    public static function isSupported();
}