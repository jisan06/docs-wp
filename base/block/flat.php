<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/easydoc for the canonical source repository
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

class BlockFlat extends Base\BlockFragment
{
    protected function _initialize(Library\ObjectConfig $config)
    {
		$t = $config->object_manager->getObject('translator');

		$config->append([
			'title'       =>  'EasyDocs flat list',
			'description' => 'Renders a flat list of documents from one or more categories',
			'controller'  => 'com://site/easydoc.controller.flat',
			'endpoint'    => '~documents',
			'icon'        => 'admin-plugins',
			'category'    => 'widgets',
			'shortcode'   => true,
			'parameters'  => ['dmsearch' => 'search', 'dmsearch_contents' => 'search_contents'],
			'script'      => 'com:easydoc.block.script.flat',
			'attributes'  => [
				'category'                 => [
					'type'     => 'array',
					'control'  => 'autocomplete',
					'default'  => null,
					'label'    => $t('Category'),
					'request'  => 'category',
					'multiple' => true,
					'resource' => 'category'
				],
				'tags'                     => [
					'type'     => 'array',
					'control'  => 'autocomplete',
					'default'  => null,
					'label'    => $t('Tags'),
					'multiple' => true,
					'value'    => 'slug',
					'resource' => 'tag'
				],
				'parent_category'          => [
					'alias'      => 'category',
					'referrable' => true
				],
				'show_parent_children'     => [
					'alias'      => 'category_children',
					'referrable' => true
				],
				'view'                     => [
					'type'    => 'string',
					'default' => 'flat',
					'request' => 'view'
				],
				'category_cache'           => [
					'type' => 'array'
				],
				'layout'                   => [
					'className' => 'flat_layout_selector',
					'type'      => 'string',
					'control'   => 'select',
					'default'   => 'table',
					'label'     => $t('Layout'),
					'options'   => [
						['label' => $t('EasyDoclayout table'), 'value' => 'table'],
						['label' => $t('EasyDoclayout list'), 'value' => 'list'],
						['label' => $t('EasyDoclayout gallery'), 'value' => 'gallery']
					],
					'request'   => 'layout'
				],
				'download_in_blank_page'   => [
					'type'    => 'boolean',
					'control' => 'toggle',
					'default' => false,
					'label'   => $t('Download in blank page'),
					'help'    => $t('Download in blank page description')
				],
				'allow_multi_download'     => [
					'className' => 'k-visibility_list__hidden',
					'type'      => 'boolean',
					'control'   => 'toggle',
					'default'   => true,
					'label'     => $t('Allow multi download'),
					'help'      => $t('Allow multi download description')
				],
				'show_pagination'          => [
					'className' => 'show_pagination_control',
					'type'    => 'boolean',
					'control' => 'toggle',
					'default' => false,
					'label'   => $t('Show pagination'),
					'help'    => $t('Displays a paginator at the bottom of the list')
				],
				'show_action_buttons'      => [
					'type'    => 'boolean',
					'control' => 'toggle',
					'default' => true,
					'label'   => $t('Show action buttons'),
					'help'    => $t('Renders the toolbar and action buttons within the list')
				],
				'documents_per_page'       => [
					'control' => 'input',
					'type'    => 'string',
					'label'   => $t('Documents limit'),
					'default' => 20,
					'size'    => 'default',
					'request' => 'limit'
				],
				'show_document_search'     => [
					'className' => 'k-visibility_pagination__show',
					'type'    => 'boolean',
					'control' => 'toggle',
					'default' => false,
					'label'   => $t('Show document search')
				],
				'show_document_sort_limit' => [
					'className' => 'k-visibility_pagination__show',
					'type'    => 'boolean',
					'control' => 'toggle',
					'default' => false,
					'label'   => $t('Show document sort limit')
				],
				'show_document_created'    => [
					'className' => 'k-visibility_gallery__hidden',
					'type'      => 'boolean',
					'control'   => 'toggle',
					'default'   => true,
					'label'     => $t('Show published date')
				],
				'show_document_created_by' => [
					'className' => 'k-visibility_gallery__hidden k-visibility_table__hidden',
					'type'      => 'boolean',
					'control'   => 'toggle',
					'default'   => true,
					'label'     => $t('Show creator name')
				],
				'show_document_modified'   => [
					'className' => 'k-visibility_gallery__hidden k-visibility_table__hidden',
					'type'      => 'boolean',
					'control'   => 'toggle',
					'default'   => true,
					'label'     => $t('Show last modified date')
				],
				'show_document_filename'   => [
					'className' => 'k-visibility_gallery__hidden k-visibility_table__hidden', // TODO Remove table__hidden for document view/preview
					'type' => 'boolean',
					'control'   => 'toggle',
					'default'   => true,
					'label'     => $t('Show file name')
				],
				'show_document_size'       => [
					'className' => 'k-visibility_gallery__hidden',
					'type'      => 'boolean',
					'control'   => 'toggle',
					'default'   => true,
					'label'     => $t('Show document size')
				],
				'show_document_hits'       => [
					'className' => 'k-visibility_gallery__hidden',
					'type'      => 'boolean',
					'control'   => 'toggle',
					'default'   => true,
					'label'     => $t('Show document hits')
				],
				'show_document_extension'  => [
					'className' => 'k-visibility_gallery__hidden',
					'type'      => 'boolean',
					'control'   => 'toggle',
					'default'   => true,
					'label'     => $t('Show document extension')
				],
				'track_downloads'          => [
					'type'    => 'boolean',
					'control' => 'toggle',
					'default' => true,
					'label'   => $t('Track downloads')
				],
				'force_download'           => [
					'type'    => 'boolean',
					'control' => 'toggle',
					'default' => false,
					'label'   => $t('Force download'),
					'help'    => $t('Force download description')
				],
				'category_children'        => [
					'type'    => 'boolean',
					'control' => 'toggle',
					'default' => true,
					'label'   => $t('Include child categories')
				],
				'sort_documents'           => [
					'type'    => 'string',
					'control' => 'select',
					'default' => 'title',
					'label'   => $t('Sort documents by'),
					'options' => [
						['label' => $t('Title alphabetical'), 'value' => 'title'],
						['label' => $t('Title reverse alphabetical'), 'value' => 'reverse_title'],
						['label' => $t('Most recent first'), 'value' => 'reverse_created_on'],
						['label' => $t('Oldest first'), 'value' => 'created_on'],
						['label' => $t('Most popular first'), 'value' => 'reverse_hits'],
						['label' => $t('Last modified first'), 'value' => 'reverse_touched_on'],
						['label' => $t('Custom'), 'value' => 'ordering']
					]
                ],
                'show_document_recent'     => [
					'type'    => 'boolean',
					'control' => 'toggle',
					'default' => false,
					'label'   => $t('Mark recent documents')
				],
                'show_document_popular'    => [
					'type'    => 'boolean',
					'control' => 'toggle',
					'default' => false,
					'label'   => $t('Mark popular documents')
				],
                'days_for_new'             => [
					'control' => 'input',
					'type'    => 'string',
					'label'   => $t('Days to mark documents as new'),
					'default' => 7,
					'size'    => 'default'
				],
                'hits_for_popular'         => [
					'control' => 'input',
					'type'    => 'string',
					'label'   => $t('Downloads to mark document as popular'),
					'default' => 100,
					'size'    => 'default'
				]
			]
		]);

        parent::_initialize($config);
    }

    public function getNamespace()
    {
        return 'easydoc';
    }

    public function setController(Library\ControllerInterface $controller, $context)
    {
		$controller->category(Library\ObjectConfig::unbox($context->attributes->category))
				->category_children($context->attributes->category_children);

        if (!$this->getObject('user')->capable('publish_posts')) {
            $controller->enabled(1)->status('published');
        }

        return parent::setController($controller, $context);
    }

	protected function _setQueryParameters(Library\ControllerInterface $controller, $context)
	{
		if (!$context->attributes->show_pagination)
		{
			// Disable some request driven options as well
			
			$attributes = ['show_document_search', 'show_document_sort_limit'];

			foreach ($attributes as $attribute) {
				$context->attributes->{$attribute} = false;
			}
		}
		else parent::_setQueryParameters($controller, $context); // Set query parameters as usual
	}
}
