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
 * Timestamp Filter
 *
 * Validates or sanitizes a value is an ISO 8601 timestamp string.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Filter
 */
class FilterTimestamp extends FilterAbstract implements FilterTraversable
{
    /**
     * Validates that the value is an ISO 8601 timestamp string.
     *
     * The format is "yyyy-mm-ddThh:ii:ss" (note the literal "T" in the middle, which acts as a
     * separator -- may also be a space). As an alternative, the value may be an array with all
     * of the keys for `Y, m, d, H, i`, and optionally `s`, in which case the value is converted
     * to an ISO 8601 string before validating it.
     *
     * Also checks that the date itself is valid (for example, no Feb 30).
     *
     * @param mixed $value The value to validate.
     * @return  bool  True when the variable is valid
     */
    public function validate($value)
    {
        // look for YmdHis keys?
        if (is_array($value)) {
            $value = $this->_arrayToTimestamp($value);
        }

        // correct length?
        if (strlen($value) != 19) {
            return false;
        }

        // valid date?
        $date = substr($value, 0, 10);
        if (! $this->_validateIsoDate($date)) {
            return false;
        }

        // valid separator?
        $sep = substr($value, 10, 1);
        if ($sep != 'T' && $sep != ' ') {
            return false;
        }

        // valid time?
        $time = substr($value, 11, 8);
        if (! $this->_validateIsoTime($time)) {
            return false;
        }

        return true;
    }

    /**
     * Forces the value to an ISO-8601 formatted timestamp using a space separator ("yyyy-mm-dd hh:ii:ss") instead of
     * a "T" separator.
     *
     * @param mixed $value The value to be sanitized.  If an integer, it is used as a Unix timestamp;
     *                     otherwise, converted to a Unix timestamp using [[php::strtotime() | ]].
     *                     If an array, and it has *all* the keys for `Y, m, d, h, i, s`, then the
     *                     array is converted into an ISO 8601 string before sanitizing.
     * @return  string The sanitized value.
     */
    public function sanitize($value)
    {
        // look for YmdHis keys?
        if (is_array($value)) {
            $value = $this->_arrayToTimestamp($value);
        }

        $result = '0000-00-00 00:00:00';
        if (!(empty($value) || $value == $result))
        {
            $format = 'Y-m-d H:i:s';
            if (is_int($value)) {
                $result = date($format, $value);
            } else {
                $result = date($format, strtotime($value));
            }
        }

        return $result;
    }

    /**
     * Converts an array of timestamp parts to a string timestamp.
     *
     * @param array $array The array of timestamp parts.
     * @return string
     */
    protected function _arrayToTimestamp($array)
    {
        $value = $this->_arrayToDate($array)
               . ' '
               . $this->_arrayToTime($array);

        return trim($value);
    }

    /**
     * Converts an array of date parts to a string date.
     *
     * @param array $array The array of date parts.
     * @return string
     */
    protected function _arrayToDate($array)
    {
        $date = array_key_exists('Y', $array) &&
                trim($array['Y']) != '' &&
                array_key_exists('m', $array) &&
                trim($array['m']) != '' &&
                array_key_exists('d', $array) &&
                trim($array['d']) != '';

        if (! $date) {
            return '';
        }

        return $array['Y'] . '-'
             . $array['m'] . '-'
             . $array['d'];
    }

    /**
     * Converts an array of time parts to a string time.
     *
     * @param array $array The array of time parts.
     * @return string
     */
    protected function _arrayToTime($array)
    {
        $time = array_key_exists('H', $array) &&
                trim($array['H']) != '' &&
                array_key_exists('i', $array) &&
                trim($array['i']) != '';

        if (! $time) {
            return '';
        }

        $s = array_key_exists('s', $array) && trim($array['s']) != ''
           ? $array['s']
           : '00';

        return $array['H'] . ':'
             . $array['i'] . ':'
             . $s;
    }

    /**
     * Validates that the value is an ISO 8601 date string.
     *
     * The format is "yyyy-mm-dd".  Also checks to see that the date itself is valid (for example, no Feb 30).
     *
     * @param string $value The value to validate.
     * @return bool True if valid, false if not.
     *
     */
    protected function _validateIsoDate($value)
    {
        // Test if value is blank.
        if(trim($value) != '')
        {
            // Test basic date format (yyyy-mm-dd)
            $pattern = '/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/D';
            $return  = preg_match($pattern, $value, $matches) && checkdate($matches[2], $matches[3], $matches[1]);
        }
        else $return = false;

        return $return;
    }

    /**
     * Validates that the value is an ISO 8601 time string (hh:ii::ss format).
     *
     * Per note from Chris Drozdowski about ISO 8601, allows two midnight times ... 00:00:00 for the beginning of the
     * day, and 24:00:00 for the end of the day.
     *
     * @param string $value The value to validate.
     * @return bool True if valid, false if not.
     *
     */
    protected function _validateIsoTime($value)
    {
        $pattern = '/^(([0-1][0-9])|(2[0-3])):[0-5][0-9]:[0-5][0-9]$/D';
        $return  = preg_match($pattern, $value, $matches) || $value == '24:00:00';

        return $return;
    }
}
