<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<?= helper('ui.load'); ?>

<? if (option('track_downloads')): ?>
    <?= helper('behavior.download_tracker'); ?>
<? endif; ?>

<div class="easydoc_document_layout">


    <? // Document | Import partial template from document view ?>
    <?= import('com://site/easydoc/document/document.html', [
        'document' => $document,
        'heading'  => '1',
        'buttonstyle' => 'btn-primary',
        'link'     => 1
    ]) ?>

</div>