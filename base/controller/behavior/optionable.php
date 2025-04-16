<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

/**
 * Used by the node controller to change document paths after moving files
 */
class ControllerBehaviorOptionable extends Base\ControllerBehaviorOptionable
{
	protected function _initialize(Library\ObjectConfig $config)
	{
		$config->append([
			'block_defaults' => [
				'direction_categories'      => 'asc',
				'document_title_link'       => 'download',
				'show_document_title'       => true,
				'show_document_owner_label' => true,
				'show_document_description' => true,
				'show_document_icon'        => true,
				'show_document_image'       => true,
				'show_document_category'    => true,
				'show_documents_header'     => false,
				'show_category_owner_label' => true,
				'show_categories_header'    => false,
				'show_image'                => true,
				'show_description'          => true,
				'show_player'               => true,
				'preview_with_gdocs'        => false,
				'search_by'                 => null,
				'category_id'               => 0,
				'category_children'         => false
			]
		]);

		parent::_initialize($config);
	}
}
