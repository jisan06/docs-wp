<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/easydoc for the canonical source repository
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class BlockTree extends BlockList
{
    protected function _initialize(Library\ObjectConfig $config)
    {
		$t = $config->object_manager->getObject('translator');

        $config->append([
            'title'       =>  'EasyDocs tree list',
            'description' => 'Renders a tree list of categories for acessing documents',
            'script'      => 'com:easydoc.block.script.tree',
			'attributes'  => [
				'view'                     => [
					'type'    => 'string',
					'default' => 'tree',
					'request' => 'view'
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
        return 'easydoc-tree';
    }

    public function beforeSave($context)
    {
        // Clear tree cache each time the page is saved as tree item routing depend on the parent category that's set on the block options

        $this->getObject('com:easydoc.database.table.categories')->clearCache();

        parent::beforeSave($context);
    }
}
