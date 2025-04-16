<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

/**
 * Document controller permission class
 */
class ControllerPermissionDocument extends ControllerPermissionAbstract
{
    public function canAdd()
    {
        $request = $this->getMixer()->getRequest();

		if ($request->isSafe())
		{
			$model = $this->getObject('com:easydoc.model.categories');

			$parent   = null;
			$children = null;

			$controller = $this->getMixer();

			if ($controller->isOptionable())
			{
				$options = $this->getOptions();

				$parent   = Library\ObjectConfig::unbox($options->parent_category);
				$children = $options->show_parent_children;
			}

			$model = $this->getObject('com:easydoc.model.categories')
						->user($controller->getUser()->getId())
						->permission('upload_document');

			if ($parent)
			{
				if ($children) {
					$model->parent_id($parent)->include_self(true); // Restrict to parent and sub-categories under the parent
				} else {
					$model->id($parent); // Need to go through the model API check since there might be multiple parents (flat)
				}
			}

			$result = $model->allowed();
		}
		else
		{
			$category = null;

			if ($request->isPost() && !empty($category_id = $request->getData()->easydoc_category_id)) {
				$category = $category_id;
			}

			$result = $this->_canExecute('upload_document', $category);
		}

        return $result;
	}

    public function canEdit()
    {
        return $this->getModel()->fetch()->canEditDocument();
    }

    public function canDelete()
    {
        return $this->_canExecuteList('delete_document');
    }
}
