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
 * Model Context
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Model\Context
 */
class ModelContext extends Command implements ModelContextInterface
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
        if($attributes instanceof ModelContextInterface)
        {
            $this->setSubject($attributes->getSubject());
            $this->setName($attributes->getName());
        }
    }

    /**
     * Set the model state
     *
     * @param ModelStateInterface $state
     * @return ModelContext
     */
    public function setState($state)
    {
        return ObjectConfig::set('state', $state);
    }

    /**
     * Get the model data
     *
     * @return ModelStateInterface
     */
    public function getState()
    {
        return ObjectConfig::get('state');
    }

    /**
     * Get the identity key
     *
     * @return mixed
     */
    public function getIdentityKey()
    {
        return ObjectConfig::get('identity_key');
    }

    /**
     * Set the identity key
     *
     * @param mixed $value
     * @return ModelContext
     */
    public function setIdentityKey($value)
    {
        return ObjectConfig::set('identity_key', $value);
    }
}