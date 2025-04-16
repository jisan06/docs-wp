<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelBehaviorCategoryPermissible extends ModelBehaviorPermissible
{
	public function onMixin(Library\ObjectMixable $mixer)
    {
        parent::onMixin($mixer);

        $mixer->getState()
			  ->insert('documents_access', 'boolean', false, false, [], true) // Allows for categories permission filtering on documents permissions only effectively filtering categories containing documents the current user can view
			  ->insert('documents_count_access', 'boolean', true, false, [], true); // Allows for disabling document count based on permissions (providing the true amount of documents in the categories)
    }

	protected function _buildPermissionQuery(Library\ModelContextInterface $context)
	{
		$state = $context->getState();

		if ($state->document_access) {
			$context->_access_permission = 'view_document';
		}

		parent::_buildPermissionQuery($context);
	}

	protected function _getOwnerCondition(Library\ModelContextInterface $context)
	{
		$condition = false;

		// Only category actions should be tested against ownership with the exception of upload documents.
		// All other xxx_document_own actions should be simply ignored since those checks require testing
		// against documents.

		$parts = explode('_', $context->_action);

		if ($parts['1'] == 'category' || $context->_action == 'upload_document') {
			$condition = parent::_getOwnerCondition($context);
		}

		return $condition;
	}

	public function setDocumentsCountQuery(Library\DatabaseQuerySelect $query, $suffix = '')
	{
		$state = $this->getState();

		$date = $this->getObject('date')->format('Y-m-d H:i:s');

		if (is_numeric($state->access) && $state->documents_count_access)
        {

			$query->join(['permissions' => 'easydoc_categories_permissions'], sprintf('COALESCE(categories%1$s.inherit_permissions, categories%1$s.easydoc_category_id) = permissions.easydoc_category_id', $suffix));

			$condition = sprintf('(permissions.wp_user_id = :access AND permissions.allowed = :allowed AND (permissions.action = :action OR (permissions.action = :action_own AND documents%s.created_by = :access)))',  $suffix);

			$default = ModelEntityPermission::getDefaultPermissions();
			$user    = $this->getObject('user.provider')->findUser($state->access);

			if (isset($default['view_document']))
			{
				$allowed_groups = $default['view_document'];

				$registered = in_array(ModelEntityUsergroup::FIXED['registered']['id'], $allowed_groups);
				$public     = in_array(ModelEntityUsergroup::FIXED['public']['id'], $allowed_groups);
				$match      = !empty(array_intersect($user->getGroups(), array_map('intval', $allowed_groups)));
				$owner      = in_array(ModelEntityUsergroup::FIXED['owner']['id'], $allowed_groups);

				if ($public || ($registered && !empty($user->getId())) || $match) {
					$condition .= ' OR ISNULL(permissions.easydoc_category_id)'; // Public || Registered || Set and match
				} elseif ($owner) {
					$condition .= sprintf(' OR (ISNULL(permissions.easydoc_category_id) AND documents%s.created_by = :access)', $suffix); // Make ownership check if owner is a default option
				}
			}

			$query->where(sprintf('(%s)', $condition))
				->bind([
					'access'     => $state->access,
					'allowed'    => 1,
					'action'     => ModelEntityPermission::getActionId('view_document'),
					'action_own' => ModelEntityPermission::getActionId('view_document_own')]);
        }

		if ($state->enabled)
		{
			// Enabled and  published conditions

			$conditions = sprintf('documents%s.enabled = :enabled', $suffix);

			$conditions .= sprintf(' AND ((documents%1$s.publish_on IS NULL OR documents%1$s.publish_on <= :publish_date)', $suffix);
			$conditions .= sprintf(' AND (documents%1$s.unpublish_on IS NULL OR documents%1$s.unpublish_on >= :unpublish_date))', $suffix);

			// Documents should be visible to those who can see them regardless of the published state if the user owns them OR the category containing them

			if (is_numeric($state->access)) {
				$conditions = sprintf('documents%1$s.created_by = :access OR categories%1$s.created_by = :access OR (%2$s)', $suffix, $conditions);
			}

			$query->where(sprintf('(%s)', $conditions))->bind(array(
				'enabled'        => 1,
				'access'         => $state->access,
				'publish_date'   => $date,
				'unpublish_date' => $date
			));
		}
	}
}
