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

class BlockScriptSearch extends Base\BlockScriptExternal
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->setPath('base/resources/assets/js/block/search.js');
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
                'Document options',
                'Layout',
                'Link to',
            ]
        ]); ?>
            ";

        $template->render($string, [
        ], 'php');

    }
}