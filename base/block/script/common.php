<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/easydoc for the canonical source repository
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\WP;

class BlockScriptCommon
{
    public static function addCommonScripts()
    {
        static $enqueued = false;

        if (!$enqueued)
        {
            \Foliokit::getObject('translator')->load('com:easydoc');

            $view = \Foliokit::getObject('com:easydoc.view.default.html');
            $template = $view->getTemplate()
                ->addFilter('style')
                ->addFilter('script');

            $string = "
            <?= helper('behavior.foliokit'); ?>
            <?= helper('behavior.select2'); ?>
            <?= helper('behavior.autocomplete', [
                'options' => [
                    'url' => 'https://example.com/' // required option
                ]
            ]); ?>
            <?= helper('translator.script', [
                'strings' => [
                    'EasyDocmenu documents'
                ]
            ]); ?>
            ";

            $template->render($string, [
            ], 'php');

            WP::wp_enqueue_script('easydoc-blocks-shared-js', EASY_DOCS_URL.'base/resources/assets/js/block/common.js', [
                'wp-dom-ready',
                'wp-blocks',
                'wp-i18n',
                'wp-editor',
                'wp-element',
                'wp-components',]);

            $enqueued = true;
        }

    }
}