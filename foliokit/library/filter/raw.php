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
 * Raw Filter
 *
 * Always validates and returns the raw variable
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Filter
 */
class FilterRaw extends FilterAbstract
{
    /**
     * Validate a value
     *
     * @param   mixed  $value Variable to be validated
     * @return  bool    True when the variable is valid
     */
    public function validate($value)
    {
        return true;
    }

    /**
     * Sanitize a value
     *
     * @param   mixed  $value Variable to be sanitized
     * @return  mixed
     */
    public function sanitize($value)
    {
        return $value;
    }
}
