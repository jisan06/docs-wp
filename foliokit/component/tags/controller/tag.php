<?php
/**
 * FolioKit Tags
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Tags;

use EasyDocLabs\Library;

/**
 * Tag Controller
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package Koowa\Component\Tags
 */
class ControllerTag extends Library\ControllerModel
{
    /**
     * Constructor.
     *
     * @param Library\ObjectConfig $config Configuration options.
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'model' => 'com:tags.model.tags'
        ]);

        //Alias the permission
        $permission         = $this->getIdentifier()->toArray();
        $permission['path'] = ['controller', 'permission'];

        $this->getObject('manager')->registerAlias($permission, 'com:tags.controller.permission.tag');

        parent::_initialize($config);
    }

    /**
     * Get the model object attached to the controller
     *
     * This method will set the model table name to [component]_tags
     *
     * @throws  \UnexpectedValueException   If the model doesn't implement the ModelInterface
     * @return  ModelTags
     */
    public function getModel()
    {
        if(!$this->_model instanceof Library\ModelInterface)
        {
            $package = $this->getIdentifier()->package;
            $this->_model = $this->getObject($this->_model, ['table' => $package.'_tags']);

            //Inject the request into the model state
            $this->_model->setState($this->getRequest()->query->toArray());
        }

        return $this->_model;
    }
}
