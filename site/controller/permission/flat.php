<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\EasyDoc;

/**
 * Flat controller permissions
 */
class ControllerPermissionFlat extends EasyDoc\ControllerPermissionAbstract
{
	protected $_actions_map = ['documents' => ['download_document', 'edit_document', 'delete_document'], 'categories' => ['upload_document']];

    public function canAdd()
    {
        return $this->canUpload();
    }

    public function canUpload()
    {
        return $this->_canExecute('upload_document'); // For checking if documents can be uploaded on ANY category
    }

    public function canDownload()
    {
        return $this->_canExecute('download_document'); // For checking if downloads on current category are allowed
    }

    public function canEdit()
    {
        return $this->_canExecute('edit_document');
    }

    public function canDelete()
    {
        return $this->_canExecute('delete_document');
    }

	protected function _mapAction($action)
	{
		$result = false;

		foreach ($this->_actions_map as $model => $actions)
		{
			if (in_array($action, $actions))
			{
				$result = $model;
				break;
			}
		}

		return $result;
	}

    protected function _canExecute($actions, $entity = null, $strict = false)
    {
        $controller = $this->getMixer();

		$include_children = $controller->getOptions()->category_children;
		$category         = $controller->getRequest()->getQuery()->category;

		$actions = (array) $actions;

		$model_actions = [];

		foreach ($actions as $action)
		{
			if ($model = $this->_mapAction($action))
			{
				if (!isset($model_actions[$model])) $model_actions[$model] = [];

				$model_actions[$model][] = $action;
			}
		}

		$result = false;

		foreach ($model_actions as $model => $actions)
		{
			$model = $this->getObject(sprintf('com:easydoc.model.%s', $model));

			switch ($model->getIdentifier()->getName())
			{
				case 'categories':

					if ($category)
					{
						$model->parent_id($category)
							->include_self(true)
							->permission($actions)
							->strict($strict)
							->user($this->getMixer()->getUser()->getId());

						if (!$include_children) {
							$model->level(0); // We need to restrict categories to the current level only
						}

						$result = (bool) $model->allowed();
					}
					else $result = parent::_canExecute($actions, $entity, $strict); // Check against all categories

					break;

				case 'documents':

					$model->permission($actions)
						->strict($strict)
						->user($this->getMixer()->getUser()->getId());

					if ($category)
					{
						$model->category($category);

						if ($include_children) {
							$model->category_children(true); // Include children
						}
					}

					$result = (bool) $model->allowed();

					break;

				default:

					break;
			}

			if ($result && !$strict) break;
		}

        return $result;
    }
}
