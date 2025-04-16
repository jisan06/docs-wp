<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;
use EasyDocLabs\WP;

/**
 * Abstract controller permission class
 */
abstract class ControllerPermissionAbstract extends Base\ControllerPermissionAbstract
{
    /**
     * Determines if the current user can execute any of the provided actions
     *
     * @param array $actions The actions to be checked against permissions
     *
     * @return bool True if any of the provided actions can be executed by the current user, false otherwise
     */
    public function canExecuteAny($actions)
    {
        return $this->_canExecute($actions, null, false);
    }

    /**
     * Checks if an action is allowed for execution.
     *
     * @param string                        $action   The action to check against.
     * @param int|ModelEntityInterface|null $entity   A model entity or ID. If null is provided, then the permissions
     *                                                check passes if the actions can be executed on at least one category
     * @param bool                          $strict   If the check is strict then the category must pass checks for all
     *                                                actions. When false is passed the check is succesful if at least
     *                                                one action can be executed over the category.
     *
     * @return bool True if the action is allowed, false otherwise.
     */
    protected function _canExecute($actions, $entity = null, $strict = false)
    {
        $actions = (array) $actions;

        $user = $this->getMixer()->getUser();

		if (!$user->isAdmin())
		{
			if (is_null($entity))
			{
				$model = $this->getObject('com:easydoc.model.categories')->permission($actions)->strict($strict)->user($user->getId());

				$result = (bool) $model->allowed();

				if (!$result && (!$strict || count($actions) === 1) && in_array('add_category', $actions)) {
					$result = $user->canAddCategory(); // Also include a default check (user may be able to create categories on root through default permissions)
				}
			}
			else
			{
				if ($entity instanceof Library\ModelEntityInterface)
				{
					$model = $this->getObject(sprintf('com:easydoc.model.%s', Library\StringInflector::pluralize($entity->getIdentifier()->getName())));
					$id    = $entity->id;
				}
				else
				{
					$model = $this->getObject('com:easydoc.model.categories');
					$id    = $entity;
				}

				$result = (bool) $model->permission($actions)
									->strict($strict)
									->user($user->getId())
									->id($id)
									->allowed();
			}
		}
		else $result = true;

        return $result;
    }

    /**
     * Performs permission checks against an entity list
     *
     * @param string $action   The action to check against
     * @param mixed  $entities A list of entities to check, null if these should be fetched on the fly
     *
     * @return bool True if all entities pass the permission check, false otherwise
     */
    protected function _canExecuteList($action, $entities = null)
    {
        $result = false;

        if (is_null($entities)) $entities = $this->_getEntities();

        if (count($entities))
        {
            foreach ($entities as $entity)
            {
                $result = $this->_canExecute($action, $entity);

                if (!$result) {
                    break;
                }
            }
        }
        else $result = $this->_canExecute($action);

        return $result;
    }

    /**
     * This will return a list of resources that the controller will act on.
     *
     * @return KModelEntityInterface
     */
    protected function _getEntities()
    {
        $model = clone $this->getModel();

        $state = $this->getModel()->getState()->getValues(true);

        if (!empty($state)) {
            $entities = $model->setState($state)->fetch();
        } else {
            $entities = [];
        }

        return $entities;
    }

	public function canRender()
	{
		$result = true;

		$controller = $this->getMixer();
		$request    = $controller->getRequest();
		$model      = clone $this->getModel();

		if ($model->getState()->isUnique() && (WP::is_admin() || $request->getQuery()->layout == 'form'))
		{
			$action = sprintf('edit_%s', Library\StringInflector::singularize($model->getIdentifier()->getName()));

			$result = $this->_canExecuteList($action, $model->fetch());
		}

		return $result;
	}
}
