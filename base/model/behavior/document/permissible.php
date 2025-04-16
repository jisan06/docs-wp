<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelBehaviorDocumentPermissible extends ModelBehaviorPermissible
{
	protected function _initialize(Library\ObjectConfig $config)
	{
		$config->append(['alias' => 'categories_permissible', 'name' => 'document', 'access_action' => 'view_document']);

		parent::_initialize($config);
	}

	protected function _beforeFetch(Library\ModelContextInterface $context)
    {
		$context->getQuery()->join([$this->_alias => 'easydoc_categories'], sprintf('tbl.easydoc_category_id = %s.easydoc_category_id', $this->_alias));

		parent::_beforeFetch($context);
	}

	public function setOwnerAccessConditions($conditions)
	{
		$state = $this->getMixer()->getState();

		// Documents should be visible to those who can see them regardless of the published state if the user owns them OR the category containing them

		if (is_numeric($state->access)) {
			$conditions = sprintf('(tbl.created_by = :user OR categories_permissible.created_by = :user) OR (%s)', $conditions);
		}

		return array($conditions, ['user' => $state->access]);
	}
}
