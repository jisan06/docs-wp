<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

/**
 * Category controller permission class
 */
class ControllerPermissionCategory extends ControllerPermissionAbstract
{
    public function canAdd()
    {
		$model      = $this->getObject('com:easydoc.model.categories');
		$controller = $this->getMixer();
		$request    = $controller->getRequest();
		$user       = $controller->getUser();

		if ($request->isSafe())
		{
			$parent   = null;
			$children = null;

			if ($controller->isOptionable())
			{
				$options = $this->getOptions();

				$parent   = $options->parent_category;
				$children = $options->show_parent_children;
			}

			if ($parent)
			{
				if ($children) {
					$result = $model->user($user->getId())
									->permission('add_category')
									->parent_id($parent)
									->include_self(true)
									->allowed();
				} else {
					$result = $model->id($parent)->fetch()->canAdd($user);
				}
			}
			else $result = $model->user($user->getId())->permission('add_category')->allowed();

			if (!$result && !$parent) {
				$result = $user->canAddCategory(); // Also include a default check (user may be able to create categories on root through default permissions)
			}
		}
		else
		{
			// Assuming POST request

			if ($category = $request->getData()->parent_id) {
				$result = $model->id($category)->fetch()->canAdd($user);
			} else {
				$result = $user->canAddCategory(); // If parent is not set then the category is being added in root, check against default permissions
			}
		}

        return $result;
    }

    public function canUpload($category = null)
    {
        return $this->_canExecute('upload_document', $category); // For checking if documents can be uploaded on ANY category
    }

    public function canDownload()
    {
        return $this->_canExecuteList('download'); // For checking if downloads on current category are allowed
    }

    public function canEdit()
    {
        return $this->_canExecuteList('edit_category');
    }

    public function canDelete()
    {
        return $this->_canExecuteList('delete_category');
    }
}
