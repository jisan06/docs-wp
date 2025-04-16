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
 * Template Context
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Template\Context
 */
class TemplateContext extends Command implements TemplateContextInterface
{
    /**
     * Constructor.
     *
     * @param  array|\Traversable  $attributes An associative array or a Traversable object instance
     */
    public function __construct($attributes = array())
    {
        ObjectConfig::__construct($attributes);

        //Set the subject and the name
        if($attributes instanceof TemplateContextInterface)
        {
            $this->setSubject($attributes->getSubject());
            $this->setName($attributes->getName());
        }
    }

    /**
     * Set the view data
     *
     * @param array $data
     * @return ObjectConfigInterface
     */
    public function setData($data)
    {
        return ObjectConfig::set('data', $data);
    }

    /**
     * Get the view data
     *
     * @return array
     */
    public function getData()
    {
        return ObjectConfig::get('data');
    }

    /**
     * Set the template source
     *
     * @param string $source
     * @return ObjectConfigInterface
     */
    public function setSource($source)
    {
        return ObjectConfig::set('source', $source);
    }

    /**
     * Get the template source
     *
     * @return string
     */
    public function getSource()
    {
        return ObjectConfig::get('source');
    }

    /**
     * Set the view parameters
     *
     * @param array|ObjectConfigInterface $parameters
     * @return ObjectConfigInterface
     */
    public function setParameters($parameters)
    {
        return ObjectConfig::set('parameters', $parameters);
    }

    /**
     * Get the view parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return ObjectConfig::get('parameters');
    }
}