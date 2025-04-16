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

class BlockSubmit extends Base\BlockFragment
{
    protected function _initialize(Library\ObjectConfig $config)
    {
		$t = $config->object_manager->getObject('translator');

		$config->append([
			'title'       =>  'EasyDocs submit',
			'description' => 'Renders a simple interface for uploading documents',
			'controller'  => 'com://site/easydoc.controller.submit',
			'endpoint'    => '~documents',
			'icon'        => 'admin-plugins',
			'category'    => 'widgets',
			'shortcode'   => true,
			'script'      => 'com:easydoc.block.script.submit',
			'attributes'  => [
				'category_id'         => [
					'type'     => 'array',
					'control'  => 'autocomplete',
					'default'  => null,
					'multiple' => true,
					'label'    => $t('Category'),
					'resource' => 'category',
					'text'     => 'title',
					'value'    => 'id'
				],
				'category_children'   => [
					'type'        => 'boolean',
					'control'     => 'toggle',
					'default'     => true,
					'label'       => $t('submit category children label'),
					'description' => $t('submit category children description')
				],
				'auto_publish'        => [
					'type'       => 'boolean',
					'control'    => 'toggle',
					'default'    => false,
					'referrable' => true,
					'label'      => $t('submit publish')
				],
				'show_description'    => [
					'type'        => 'boolean',
					'control'     => 'toggle',
					'default'     => false,
					'label'       => $t('Show summary field'),
					'description' => $t('Show summary field description')
				],
				'notification_emails' => [
					'type'        => 'string',
					'control'     => 'textarea',
					'referrable'  => true,
					'label'       => $t('submit notify'),
					'description' => $t('submit notify description')
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
        $layout = $this->getObject('request')->getQuery()->has('submit_success') ? 'success' : 'form';

        $controller->view('submit')->layout($layout);

        return parent::setController($controller, $context);
    }
}
