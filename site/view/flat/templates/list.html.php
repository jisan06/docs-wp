<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<?= helper('ui.load'); ?>
<?= helper('behavior.modal');?>

<? if ($can_delete_document): ?>
    <ktml:script src="assets://easydoc/site/js/items.js" />
<? endif; ?>

<? // RSS feed ?>
<link href="<?=route('format=rss');?>" rel="alternate" type="application/rss+xml" title="RSS 2.0" />

<div class="easydoc_list_layout easydoc_list_layout--filtered_list">

    <? // Toolbar ?>
    <ktml:toolbar type="actionbar">

    <? // Search ?>
    <? if (option('show_document_search')): ?>
        <?= import('com://site/easydoc/search/default.html') ?>
    <? endif ?>

    <? // Documents & pagination  ?>
    <form action="<?= url() ?>" method="get" class="k-js-grid-controller">

        <? // Sorting ?>
        <? if (option('show_document_sort_limit') && count($documents)): ?>
        <div class="easydoc_block">
            <div class="easydoc_sorting form-search">
                <label for="sort-documents" class="control-label"><?= translate('Order by') ?></label>
                <?= helper('paginator.sort_documents', [
                    'attribs'   => [
                      'class' => 'input-medium',
                      'id' => 'sort-documents'
                    ]
                ]); ?>
            </div>
        </div>
        <? endif; ?>

        <? if (count($documents)): ?>

            <? // Document list | Import child template from documents view ?>
            <?= import('com://site/easydoc/documents/list.html', [
                'documents' => $documents
            ])?>

            <? // Pagination ?>
            <? if (option('show_pagination') !== false && parameters()->total): ?>
                <div class="k-table-pagination">
                <?= helper('paginator.pagination', [
                    'show_limit' => (bool) option('show_document_sort_limit')
                ]) ?>
                </div>
            <? endif; ?>

        <? endif; ?>


    </form>
</div>
