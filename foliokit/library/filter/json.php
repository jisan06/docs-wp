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
 * Json Filter
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Filter
 */
class FilterJson extends FilterAbstract
{
    /**
     * Validate a value
     *
     * @param   mixed  $value Value to be validated
     * @return  bool    True when the variable is valid
     */
    public function validate($value)
    {
        try {
            $config = $this->getObject('object.config.factory')->fromString('json', $value);
        } catch(\RuntimeException $e) {
            $config = null;
        }

        return is_string($value) && !is_null($config);
    }

    /**
     * Sanitize a value
     *
     * @param   mixed  $value Value to be sanitized
     * @return  string
     */
    public function sanitize($value)
    {
        if(!$value instanceof ObjectConfigJson)
        {
            if(is_string($value)) {
                $value = $this->getObject('object.config.factory')->fromString('json', $value);
            } else {
                $value = $this->getObject('object.config.factory')->createFormat('json', $value);
            }
        }

        return $value;
    }
}
