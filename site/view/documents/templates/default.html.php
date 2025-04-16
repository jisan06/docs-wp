<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<? if (can('add')): ?>
    <?= helper('behavior.modal'); ?>
<? endif; ?>

<?= helper('ui.load'); ?>

<div class="easydoc_table_layout easydoc_table_layout--default">

    <? // Toolbar ?>
    <ktml:toolbar type="actionbar" />

    <? foreach ($documents as $document): ?>
    <? //Import child template from document view ?>
    <?= import('com://site/easydoc/document/default.html', [
        'document'    => $document,
        'button_size' => 'mini'
        ])?>
    <? endforeach ?>

</div>