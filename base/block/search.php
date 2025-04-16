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

class BlockSearch extends Base\BlockFragment
{
    protected function _initialize(Library\ObjectConfig $config)
    {
		$t = $config->object_manager->getObject('translator');

        $config->append([
            'title'       =>  'EasyDocs search',
            'description' => 'Renders a search text input for searching for documents',
            'controller'  => 'com://site/easydoc.controller.search',
            'endpoint'    => '~documents',
            'icon'        => 'admin-plugins',
            'category'    => 'widgets',
            'shortcode'   => true,
            'script'      => 'com:easydoc.block.script.search',
			'attributes'  => [
				'results_layout'           => [
					'className' => 'search_layout_selector',
					'type'      => 'string',
					'control'   => 'select',
					'default'   => 'table',
					'label'     => $t('Layout'),
					'options'   => [
						['label' => $t('EasyDoclayout table'), 'value' => 'table'],
						['label' => $t('EasyDoclayout list'), 'value' => 'list'],
						['label' => $t('EasyDoclayout gallery'), 'value' => 'gallery']
					]
				],
				'view'                     => [
					'type'    => 'string',
					'default' => 'search',
					'request' => 'view'
				],
				'layout'                   => [
					'type'    => 'string',
					'default' => 'default',
					'request' => 'layout'
				],
				'link_to'                  => [
					'type'    => 'string',
					'control' => 'select',
					'default' => 'download',
					'label'   => $t('Link to'),
					'options' => [
						['label' => $t('Direct download'), 'value' => 'download'],
						['label' => $t('Preview'), 'value' => 'preview']
					]
				],
				'download_in_blank_page'   => [
					'className' => 'k-visibility_gallery__hidden',
					'type'      => 'boolean',
					'control'   => 'toggle',
					'default'   => false,
					'label'     => $t('Download in blank page'),
					'help'      => $t('Download in blank page description')
				],
				'documents_per_page'       => [
					'control' => 'input',
					'type'    => 'string',
					'label'   => $t('Documents limit'),
					'default' => 20,
					'size'    => 'default',
					'request' => 'limit'
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
				]
			]
        ]);

        parent::_initialize($config);

		$config->parameters = ['dmsearch' => 'search', 'dmsearch_contents' => 'search_contents', 'limit', 'offset']; // Override params
    }

    public function setDispatcher(Library\DispatcherInterface $dispatcher, $context)
    {
        $dispatcher->addBehavior('limitable', [
            'default' => $context->attributes->documents_per_page
        ]);

        parent::setDispatcher($dispatcher, $context);
    }

    public function getNamespace()
    {
        return 'easydoc';
    }
}
