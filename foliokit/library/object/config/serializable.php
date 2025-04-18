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
 * Object Config Serializable Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Object\Config
 */
interface ObjectConfigSerializable
{
    /**
     * Read from a string and create a ObjectConfig object
     *
     * @param  string $string
     * @param  bool    $object  If TRUE return a ObjectConfig, if FALSE return an array. Default TRUE.
     * @throws \DomainException
     * @return ObjectConfigSerializable|array
     */
    public function fromString($string, $object = false);

    /**
     * Write a config object to a string.
     *
     * @return string
     */
    public function toString();

    /**
     * Read from a file and create an array
     *
     * @param  string $filename
     * @param  bool    $object  If TRUE return a ConfigObject, if FALSE return an array. Default TRUE.
     * @throws \RuntimeException
     * @return ObjectConfigSerializable|array
     */
    public function fromFile($filename, $object = false);

    /**
     * Write a config object to a file.
     *
     * @param  string  $filename
     * @return ObjectConfigSerializable
     */
    public function toFile($filename);
}