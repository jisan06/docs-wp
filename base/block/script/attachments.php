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

class BlockScriptAttachments extends Base\BlockScriptExternal
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->setPath('base/resources/assets/js/block/attachments.js');
    }

    public function beforeEnqueue()
    {
        parent::beforeEnqueue();

        BlockScriptCommon::addCommonScripts();

        $view = $this->getObject('com:easydoc.view.default.html');
        $template = $view->getTemplate()
            ->addFilter('style')
            ->addFilter('script');

        $string = "
        <?= helper('behavior.modal'); ?>
        <?= helper('translator.script', [
            'strings' => [
                'Behavior',
                'Layout',
                'Edit',
                'Select'
            ]
        ]); ?>
            ";

        $template->render($string, [
        ], 'php');

    }
}