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
 * Filename Filter
 *
 * Filter strips path info
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Filter
 */
class FilterFilename extends FilterAbstract implements FilterTraversable
{
    /**
     * Validate a value
     *
     * @param   mixed   $value Value to be validated
     * @return  bool    True when the variable is valid
     */
    public function validate($value)
    {
        return ((string) $value === $this->sanitize($value));
    }

    /**
     * Sanitize a value
     *
     * @param   mixed   $value Value to be sanitized
     * @return  string
     */
    public function sanitize($value)
    {
        // basename does not work if the string starts with a UTF character
        return \Foliokit\basename($value);
    }
}
