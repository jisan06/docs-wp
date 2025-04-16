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
 * Directory name Filter
 *
 * Calls {@link dirname()} on the passed value
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Filter
 */
class FilterDirname extends FilterAbstract implements FilterTraversable
{
    /**
     * Validate a value
     *
     * @param   mixed   $value Variable to be validated
     * @return  bool    True when the variable is valid
     */
    public function validate($value)
    {
        $value = trim($value);
        return ((string) $value === $this->sanitize($value));
    }

    /**
     * Sanitize a value
     *
     * @param   mixed   $value Variable to be sanitized
     * @return  string
     */
    public function sanitize($value)
    {
        $value = trim($value);
        return dirname($value);
    }
}
