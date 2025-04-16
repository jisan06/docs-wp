<?php
/**
 * Foliokit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Activities;

use EasyDocLabs\Library;

/**
 * Resourceable Controller Behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Ait Theme Club\Component\LOGman
 */
class ControllerBehaviorResourceable extends Library\ControllerBehaviorAbstract
{
    /**
     * A list of actions for cleaning up resources
     *
     * @var array
     */
    protected $_actions;

    /**
     * Resource controller
     *
     * @var Library\ControllerInterface
     */
    protected $_controller = 'com:activities.controller.resource';

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_actions = Library\ObjectConfig::unbox($config->actions);
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'actions'    => ['delete']
        ]);

        parent::_initialize($config);
    }

    protected function _afterAdd(Library\ControllerContextInterface $context)
    {
        $entity = $context->result;

        if ($entity instanceof ActivityInterface && $entity->getActivityObject())
        {
            $resource = $this->_getResource($entity);

            $controller = $this->_getController();

            if (!in_array($entity->action, $this->_actions))
            {
                $data = $this->_getData($entity);

                if (!$resource->isNew())
                {
                    // Update resource if title changed.
                    if ($resource->title != $entity->title) {
                        $controller->id($resource->id)->edit($data);
                    }
                }
                else $controller->add($data);
            }
            else if (!$resource->isNew()) $controller->id($resource->id)->delete();
        }
    }

    /**
     * Resource getter.
     *
     * @param Library\ModelEntityInterface $entity The entity to get the resource from
     *
     * @return Library\ModelEntityInterface|null The resource
     */
    protected function _getResource($entity)
    {
        $model = $this->_getController()->getModel();

        $model->reset()->getState()->setValues([
            'package'     => $entity->package,
            'name'        => $entity->name,
            'resource_id' => $entity->row
        ]);

        return $model->fetch();
    }

    /**
     * Entity data getter
     *
     * @param Library\ModelEntityInterface $entity The entity to get data from
     *
     * @return array The entity data
     */
    protected function _getData(Library\ModelEntityInterface $entity)
    {
        $data = [
            'package'     => $entity->package,
            'name'        => $entity->name,
            'resource_id' => $entity->row,
            'title'       => $entity->title
        ];

        if ($uuid = $entity->getActivityObject()->getUuid()) {
            $data['uuid'] = $uuid;
        }

        return $data;
    }

    /**
     * Resource controller getter.
     *
     * @return Library\ControllerInterface The controller
     */
    protected function _getController()
    {
        if (!$this->_controller instanceof Library\ControllerInterface) {
            $this->_controller = $this->getObject($this->_controller);
        }

        return $this->_controller;
    }
}