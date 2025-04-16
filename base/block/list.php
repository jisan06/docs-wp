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

class BlockList extends Base\BlockPage
{
    protected function _initialize(Library\ObjectConfig $config)
    {
		$t = $config->object_manager->getObject('translator');

		$config->append([
			'title'       =>  'EasyDocs hierarchical list',
			'description' => 'Renders a hierarchical list of categories for acessing documents',
			'icon'        => 'admin-plugins',
			'category'    => 'widgets',
			'shortcode'   => true,
			'script'      => 'com:easydoc.block.script.list',
			'attributes'  => [
                'show_breadcrumb' => [
					'type'      => 'boolean',
					'control'   => 'toggle',
					'default'   => true,
					'label'     => $t('Show breadcrumb')
				],
				'page_category'            => [
					'type'     => 'string',
					'control'  => 'autocomplete',
					'default'  => null,
					'label'    => $t('Category'),
					'value'    => 'uuid',
					'resource' => 'category'
				],
				'parent_category'          => [
					'alias'      => 'page_category',
					'filter'     => function ($value) {
						$result = $value;

						if ($value && !is_numeric($value)) {
							$category = $this->getObject('com:easydoc.model.categories')->uuid($value)->fetch();

							if (!$category->isNew()) {
								$result = $category->id;
							} else {
								$result = null;
							}
						}

						return $result;
					},
					'referrable' => true
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
				'show_parent_children'     => [
					'alias'      => 'show_subcategories',
					'referrable' => true
				],
				'show_icon'                => [
					'default' => true
				],
				'show_pagination'          => [
					'type'    => 'boolean',
					'default' => true
				],
				'view'                     => [
					'type'    => 'string',
					'default' => 'list',
					'request' => 'view'
				],
				'layout'                   => [
					'className' => 'list_layout_selector',
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
				'document_title_link' => [
					'type'    => 'string',
					'control' => 'select',
					'default' => 'download',
					'label'   => $t('Link to'),
					'options' => [
						['label' => $t('Document download'), 'value' => 'download'],
						['label' => $t('Document view'), 'value' => 'preview']
					]
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
				'documents_per_page'       => [
					'control' => 'input',
					'type'    => 'string',
					'label'   => $t('Documents limit'),
					'default' => 20,
					'size'    => 'default',
					'request' => 'limit'
				],
				'show_document_search'     => [
					'type'    => 'boolean',
					'control' => 'toggle',
					'default' => false,
					'label'   => $t('Show document search')
				],
				'show_document_sort_limit' => [
					'type'    => 'boolean',
					'control' => 'toggle',
					'default' => false,
					'label'   => $t('Show document sort limit')
				],
				'show_document_tags'       => [
					'className' => 'k-visibility_gallery__hidden',
					'type'      => 'boolean',
					'control'   => 'toggle',
					'default'   => true,
					'label'     => $t('Show document tags')
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
				'show_category_title'      => [
					'type'    => 'boolean',
					'control' => 'toggle',
					'default' => true,
					'label'   => $t('Show category title')
				],
				'show_subcategories'       => [
					'type'    => 'boolean',
					'control' => 'toggle',
					'default' => true,
					'label'   => $t('Show subcategories')
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
				'sort_categories'          => [
					'type'    => 'string',
					'control' => 'select',
					'default' => 'title',
					'label'   => $t('Sort categories by'),
					'options' => [
						['label' => $t('Title alphabetical'), 'value' => 'title'],
						['label' => $t('Title reverse alphabetical'), 'value' => 'reverse_title'],
						['label' => $t('Most recent first'), 'value' => 'reverse_created_on'],
						['label' => $t('Oldest first'), 'value' => 'created_on'],
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

    public function getShortcodeName()
    {
        return 'easydoc';
    }

    public function beforeRender($context)
    {
        parent::beforeRender($context);

        // Always overwrite so that the request value is bypassed
        $context->query->page_category = $context->attributes->page_category;

        // page_category is an integer probably coming from a shortcode
        if (is_numeric($context->query->page_category)) {
            $model = $this->getObject('com:easydoc.model.categories');
            $context->query->page_category = $model->id($context->attributes->page_category)->fetch()->uuid;
        }
    }
}
