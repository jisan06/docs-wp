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
 * Object Config Json
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Object\Config
 */
class ObjectConfigJson extends ObjectConfigFormat
{
    /**
     * The format
     *
     * @var string
     */
    protected static $_media_type = 'application/json';

    /**
     * Read from a string and create an array
     *
     * @param  string   $string
     * @param  bool     $object  If TRUE return a ObjectConfig, if FALSE return an array. Default TRUE.
     * @throws \DomainException  If the JSON cannot be decoded or if the encoded data is deeper than the recursion limit.
     * @return ObjectConfigJson|array
     */
    public function fromString($string, $object = true)
    {
        $data = array();

        if(!empty($string))
        {
            $data = json_decode($string, true);

            if (json_last_error() > 0) {
                throw new \DomainException(sprintf('Cannot decode from JSON string - %s', json_last_error_msg()));
            }
        }

        return $object ? $this->merge($data) : $data;
    }

    /**
     * Write a config object to a string.
     *
     * @return string|false    Returns a JSON encoded string on success. False on failure.
     * @throws \DomainException Object could not be encoded to valid JSON.
     */
    public function toString()
    {
        $data = $this->toArray();

        // Root should be JSON object, not array
        if (count($data) === 0) {
            $data = new \ArrayObject();
        }

        // Encode <, >, ', &, and " for RFC4627-compliant JSON, which may also be embedded into HTML.
        $data = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
        
        if($data === false) {
            throw new \DomainException('Cannot encode data to JSON string');
        }

        return $data;
    }
}