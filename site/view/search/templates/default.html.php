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

<div class="easydoc_list_layout easydoc_list_layout--default">

        <? // Documents & pagination  ?>
        <? $action = url(); unset($action->query['offset']) ?>
        <form action="<?= $action ?>" method="get" class="k-js-grid-controller">

            <? // Search ?>
            <?= import('search_form.html') ?>

        </form>

        <? if ($filter->search && isset($documents) && !count($documents)): ?>

            <? // No documents found message ?>
            <div class="alert alert-warning"><?= import('com://site/easydoc/documents/no_results.html') ?></div>

        <? endif ?>

        <? if ($filter->search && isset($show_results) && $show_results): ?>
            <? // Sorting ?>
            <? if (option('layout') !== 'table' && option('show_document_sort_limit') && count($documents)): ?>
                <div class="easydoc_sorting form-search">
                    <label for="sort-documents" class="control-label"><?= translate('Order by') ?></label>
                    <?= helper('paginator.sort_documents', [
                        'sort'      => 'document_sort',
                        'direction' => 'document_direction',
                        'attribs'   => ['class' => 'input-medium', 'id' => 'sort-documents']
                    ]); ?>
                </div>
            <? endif; ?>

            <? // Document list | Import child template from documents view ?>

            <?= import('com://site/easydoc/search/results_'.option('results_layout').'.html', [
                'documents' => $documents
            ])?>

            <? // Pagination  ?>
            <? if (parameters()->total) : ?>
                <?= helper('paginator.pagination', [
                    'show_limit' => (bool) option('show_document_sort_limit')
                ]) ?>
            <? endif; ?>
        <? endif ?>
</div>
