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
 * String Filter
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Filter
 */
class FilterString extends FilterAbstract implements FilterTraversable
{
    /**
     * Validate a value
     *
     * @param   mixed   $value Value to be validated
     * @return  bool    True when the variable is valid
     */
    public function validate($value)
    {
        $value = trim($value);
        return (is_string($value) && ($value === $this->__filter_string_polyfill($value, false)));
    }

    /**
     * Sanitize a value
     *
     * @param   mixed   $value Value to be sanitized
     * @return  string
     */
    public function sanitize($value)
    {
        return $this->__filter_string_polyfill($value, false);
    }

    /**
     * Polyfill function for FILTER_SANITIZE_STRING
     * 
     * FILTER_SANITIZE_STRING is deprecated in PHP8.1. This polyfill replicates the exact behavior of the filter.
     * 
     * 
     *
     * @param string $string
     * @return string
     */
    private function __filter_string_polyfill(?string $string, bool $encode_quotes = true): string
    {
        $str = preg_replace('/\x00|<[^>]*>?/', '', $string ?: '');
    
        return $encode_quotes ? str_replace(["'", '"'], ['&#39;', '&#34;'], $str) : $str;
    }
}

