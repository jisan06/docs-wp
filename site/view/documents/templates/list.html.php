<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<? if (option('track_downloads')): ?>
    <?= helper('behavior.download_tracker'); ?>
<? endif; ?>

<?= helper('ui.load'); ?>

<? if (can('add')): ?>
    <?= helper('behavior.modal'); ?>
<? endif; ?>

<? foreach ($documents as $document): ?>

    <? // Document | Import child template from document view ?>
    <?= import('com://site/easydoc/document/document.html', [
        'document'    => $document,
        'heading'     => '4',
        'buttonstyle' => 'btn-default',
        'link'        => (bool) ($document->canDownload() || !object('user')->isAuthentic()),
        'description' => 'summary'
    ]) ?>
<? endforeach ?>