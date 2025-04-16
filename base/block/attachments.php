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

class BlockAttachments extends Base\BlockFragment
{
    protected function _initialize(Library\ObjectConfig $config)
    {
		$t = $config->object_manager->getObject('translator');

		$config->append([
			'title'       =>  'EasyDocs attachments',
			'description' => 'Renders a selected list of document links',
			'controller'  => 'com://site/easydoc.controller.document',
			'endpoint'    => '~documents',
			'icon'        => 'admin-plugins',
			'category'    => 'widgets',
			'shortcode'   => true,
			'script'      => 'com:easydoc.block.script.attachments',
			'attributes'  => [
				'layout'                  => [
					'className' => 'attachments_layout_selector',
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
				'link_to'                 => [
					'type'    => 'string',
					'control' => 'select',
					'default' => 'download',
					'label'   => $t('Link to'),
					'options' => [
						['label' => $t('Direct download'), 'value' => 'download'],
						['label' => $t('Preview'), 'value' => 'preview']
					]
				],
				'force_download'          => [
					'type'    => 'boolean',
					'control' => 'toggle',
					'default' => false,
					'label'   => $t('Force download'),
					'help'    => $t('Force download description')
				],
				'download_in_blank_page'  => [
					'className' => 'k-visibility_gallery__hidden',
					'type'      => 'boolean',
					'control'   => 'toggle',
					'default'   => false,
					'label'     => $t('Download in blank page'),
					'help'      => $t('Download in blank page description')
				],
				'show_document_hits'      => [
					'className' => 'k-visibility_gallery__hidden',
					'type'      => 'boolean',
					'control'   => 'toggle',
					'default'   => true,
					'label'     => $t('Show document hits')
				],
				'show_document_extension' => [
					'className' => 'k-visibility_gallery__hidden',
					'type'      => 'boolean',
					'control'   => 'toggle',
					'default'   => true,
					'label'     => $t('Show document extension')
				],
				'show_document_size'      => [
					'className' => 'k-visibility_gallery__hidden',
					'type'      => 'boolean',
					'control'   => 'toggle',
					'default'   => true,
					'label'     => $t('Show document size')
				],
				'show_icon'               => [
					'className' => 'k-visibility_gallery__hidden',
					'type'      => 'boolean',
					'control'   => 'toggle',
					'default'   => true,
					'label'     => $t('Show document icon')
				],
				'show_document_created'   => [
					'className' => 'k-visibility_gallery__hidden',
					'type'      => 'boolean',
					'control'   => 'toggle',
					'default'   => true,
					'label'     => $t('Show published date')
				],
				'documents'               => [
					'type' => 'array'
				],
				'preview'                 => [
					'type' => 'array'
				],
				'_category'               => [
					'type'    => 'string',
					'default' => $this->_getCategoryInitialState()
				],
				'view'                    => [
					'type'    => 'string',
					'default' => 'documents',
					'request' => 'view'
				],
				'sort_documents'          => [
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
    }

    protected function _getCategoryInitialState()
    {
        $category_id = '';

        $category = $this->getObject('com:easydoc.model.categories')
                         ->parent_id(0)
                         ->level(1)
                         ->sort('title')
                         ->direction('asc')
                         ->limit(1)
                         ->fetch();

        if (!$category->isNew()) $category_id = $category->id;

        return $category_id;
    }

    public function getNamespace()
    {
        return 'easydoc';
    }

    protected function _canRender($context)
    {
        $result = parent::_canRender($context);

        if ($result) {
            $result = !!Library\ObjectConfig::unbox($context->attributes->documents);
        }

        return $result;
    }

    public function setController(Library\ControllerInterface $controller, $context)
    {
        $documents = Library\ObjectConfig::unbox($context->attributes->documents);

        $controller->id($documents)->layout('attachments_' . $context->attributes->layout);

        if (!$this->getObject('user')->capable('publish_posts')) {
            $controller->enabled(1)->status('published');
        }

        return parent::setController($controller, $context);
    }
}
