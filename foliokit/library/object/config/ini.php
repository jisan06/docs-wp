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
 * Object Config Ini
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Object\Config
 */
class ObjectConfigIni extends ObjectConfigFormat
{
    /**
     * The format
     *
     * @var string
     */
    protected static $_media_type = 'text/plain';

    /**
     * Read from a string and create an array
     *
     * @param  string $string
     * @param  bool    $object  If TRUE return a KConfigObjectIni, if FALSE return an array. Default TRUE.
     * @throws \DomainException
     * @return ObjectConfigIni|array
     */
    public function fromString($string, $object = true)
    {
        $data = array();

        if(!empty($string))
        {
            $data = parse_ini_string($string, true);

            if($data === false) {
                throw new \DomainException('Cannot parse INI string');
            }
        }

        return $object ? $this->merge($data) : $data;
    }

    /**
     * Write a config object to a string.
     *
     * There is no way to have ini values nested further than two levels deep.  Therefore we will only go through the
     * first two levels of the object.
     *
     * @return string|false   Returns a INI encoded string on success. False on failure.
     */
    public function toString()
    {
        $local  = array();
        $global = array();

        $data = (object) $this->toArray();

        // Iterate over the object to set the properties.
        foreach (get_object_vars($data) as $key => $value)
        {
            // If the value is an object then we need to put it in a local section.
            if (is_object($value))
            {
                // Add the section line.
                $local[] = '';
                $local[] = '[' . $key . ']';

                // Add the properties for this section.
                foreach (get_object_vars($value) as $k => $v) {
                    $local[] = $k . '=' . self::_encodeValue($v);
                }
            }
            else
            {
                // Not in a section so add the property to the global array.
                $global[] = $key . '=' . self::_encodeValue($value);
            }
        }

        return implode("\n", array_merge($global, $local));
    }

    /**
     * Encode a value for INI.
     *
     * @param  mixed $value
     * @return string
     */
    protected static function _encodeValue($value)
    {
        $string = '';

        switch (gettype($value))
        {
            case 'integer':
            case 'double':
                $string = $value;
                break;

            case 'boolean':
                $string = $value ? 'true' : 'false';
                break;

            case 'string':
                // Sanitize any CRLF characters..
                $string = '"' . str_replace(array("\r\n", "\n"), '\\n', $value) . '"';
                break;
        }

        return $string;
    }
}