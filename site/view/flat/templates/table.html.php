<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<ktml:messages>

<?= helper('ui.load'); ?>
<?= helper('behavior.modal'); ?>

<? if ($can_delete_document): ?>
    <ktml:script src="assets://easydoc/site/js/items.js" />
<? endif; ?>

<? // RSS feed ?>
<link href="<?=route('format=rss');?>" rel="alternate" type="application/rss+xml" title="RSS 2.0" />


<div class="easydoc_table_layout easydoc_table_layout--filtered_table">

    <? // Toolbar ?>
    <ktml:toolbar type="actionbar">

    <? // Search ?>
    <? if (option('show_document_search')): ?>
        <?= import('com://site/easydoc/search/default.html') ?>
    <? endif ?>
    <? // Table | Import child template from documents view ?>
    <form action="<?= url() ?>" method="get" class="k-js-grid-controller">

        <? // Document list | Import child template from documents view ?>
        <?= import('com://site/easydoc/documents/table.html', [
            'documents' => $documents,
            'state'     => parameters()
        ])?>

        <? // Pagination ?>
        <? if (option('show_pagination') !== false && parameters()->total): ?>
            <?= helper('paginator.pagination', [
                'show_limit' => (bool) option('show_document_sort_limit')
            ]) ?>
        <? endif; ?>

    </form>

</div>
