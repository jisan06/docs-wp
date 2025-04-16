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
 * Yaml Filter
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Filter
 */
class FilterYaml extends FilterAbstract
{
    /**
     * Validate a value
     *
     * @param    mixed  $value Value to be validated
     * @return   bool   True when the variable is valid
     */
    public function validate($value)
    {
        try {
            $config = $this->getObject('object.config.factory')->fromString('yaml', $value);
        } catch(\RuntimeException $e) {
            $config = null;
        }

        return is_string($value) && !is_null($config);
    }

    /**
     * Sanitize a value
     *
     * @param   mixed  $value Value to be sanitized
     * @return  ObjectConfig
     */
    public function sanitize($value)
    {
        if(!$value instanceof ObjectConfig)
        {
            if(is_string($value)) {
                $value = $this->getObject('object.config.factory')->fromString('yaml', $value);
            } else {
                $value = $this->getObject('object.config.factory')->createFormat('yaml', $value);
            }
        }

        return $value;
    }
}