<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/easydoc for the canonical source repository
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

/**
 * Summary Text Filter
 *
 * @author  Jebb Domingo <https://github.com/jebbdomingo>
 * @package EasyDocLabs\Library\Filter
 */
class FilterSummary extends Library\FilterAbstract implements Library\FilterTraversable
{
    /**
     * Maximum length of the summary
     *
     * @var integer
     */
    protected $_length;

    /**
     * Constructor
     *
     * @param Library\ObjectConfig $config An optional Library\ObjectConfig object with configuration options
     */
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_length = $config->length;
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   Library\ObjectConfig $config Configuration options
     * @return void
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'length' => 150
        ]);

        parent::_initialize($config);
    }

    /**
     * Length accessor
     *
     * @return integer
     */
    public function getLength()
    {
        return $this->_length;
    }

    /**
     * Validate a value
     *
     * @param   mixed   $value Value to be validated
     * @return  bool    True when the variable is valid
     */
    public function validate($value)
    {
        $value  = trim($value);
        $result = false;

        if ($this->getObject('lib:filter.string')->validate($value))
        {
            if (strlen($value) <= $this->getLength()) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Sanitize a value
     *
     * Limits the string
     *
     * @param   mixed   $value Variable to be sanitized
     * @return  mixed
     */
    public function sanitize($value)
    {
        // Trim separators around the slug
        $value = trim($value);

        // Limit length
        if (strlen($value) > $this->getLength()) {
            $value = substr($value, 0, $this->getLength());
        }

        return $value;
    }
}
