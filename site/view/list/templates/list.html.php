<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<ktml:script src="assets://easydoc/site/js/items.js" />

<?= helper('ui.load'); ?>
<?= helper('behavior.modal');?>
<?= helper('behavior.opengraph', array(
    'entity' => $category
)) ?>

<? // RSS feed ?>
<link href="<?=route('format=rss');?>" rel="alternate" type="application/rss+xml" title="RSS 2.0" />

<? if (option('show_breadcrumb')): ?>
    <?= helper('breadcrumb.load', ['entity' => $category]) ?>
<? endif ?>

<div class="easydoc_list_layout easydoc_list_layout--default">

    <? // Toolbar ?>
    <ktml:toolbar type="actionbar">

    <? // Category ?>
    <? if ((option('show_category_title') && $category->title)
          || (option('show_image') && $category->image)
          || ($category->description_full && option('show_description'))
    ): ?>
    <div class="easydoc_category">

        <? // Header ?>
        <? if (option('show_category_title') && $category->title): ?>
        <h3 class="koowa_header">
            <? // Header image ?>
            <? if (option('show_icon') && $category->icon): ?>
                <span class="koowa_header__item koowa_header__item--image_container">
                    <?= import('com://site/easydoc/document/icon.html', ['icon' => $category->icon, 'class' => 'k-icon--size-medium']) ?>
                </span>
            <? endif ?>

            <? // Header title ?>
            <? if (option('show_category_title')): ?>
                <span class="koowa_header__item">
                    <span class="koowa_wrapped_content">
                        <span class="whitespace_preserver">
                            <?= escape($category->title); ?>

                            <? // Label locked ?>
                            <? if ($category->canEdit() && $category->isLockable() && $category->isLocked()): ?>
                                <span class="label label-warning"><?= translate('Locked'); ?></span>
                            <? endif; ?>

                            <? // Label status ?>
                            <? if (!$category->enabled): ?>
                                <span class="label label-draft"><?= translate('Draft'); ?></span>
                            <? endif; ?>

                            <? // Label owner ?>
                            <? if (option('show_category_owner_label') && !$category->isNew() && object('user')->getId() == $category->created_by): ?>
                                <span class="label label-info"><?= translate('Owner'); ?></span>
                            <? endif; ?>
                        </span>
                    </span>
                </span>
            <? endif; ?>
        </h3>
        <? endif; ?>

        <? // Edit area | Import partial template from category view ?>
        <?= import('com://site/easydoc/category/manage.html', ['category' => $category, 'bind_listener' => false]) ?>

        <? // Category image ?>
        <? if (option('show_image') && $category->image): ?>
            <?= helper('behavior.thumbnail_modal'); ?>
            <a class="easydoc_thumbnail thumbnail" href="<?= $category->image_path ?>">
                <img src="<?= $category->image_path ?>" alt="<?= escape($category->title); ?>" />
            </a>
        <? endif ?>

        <? // Category description full ?>
        <? if ($category->description_full && option('show_description')): ?>
            <div class="easydoc_description">
                <?= prepareText($category->description_full); ?>
            </div>
        <? endif; ?>
    </div>
    <? endif; ?>

    <? // Search ?>
    <? if (option('show_document_search')): ?>
        <?= import('com://site/easydoc/search/default.html') ?>
    <? endif ?>

    <? // Sub categories ?>
    <? if (option('show_subcategories') && count($subcategories)): ?>
        <? if ($category->id && option('show_categories_header')): ?>
            <div class="easydoc_block easydoc_block--top_margin">
                <? // Header ?>
                <h3 class="koowa_header koowa_header--bottom_margin">
                    <?= translate('Categories') ?>
                </h3>
            </div>
        <? endif; ?>

        <? // Categories list ?>
        <?=import('com://site/easydoc/list/categories.html', [
            'categories' => $subcategories,
            'config'     => $config
        ])?>
    <? endif; ?>

    <? // Documents header & sorting ?>
    <? if (count($documents)): ?>
        <div class="easydoc_block">
            <? if (option('show_documents_header')): ?>
            <h3 class="koowa_header">
                <?= translate('Documents')?>
            </h3>
            <? endif; ?>
        </div>

        <? // Documents & pagination  ?>
        <form action="<?= url() ?>" method="get" class="k-js-grid-controller">

            <? // Sorting ?>
            <? if (option('show_document_sort_limit') && count($documents)): ?>
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
            <?= import('com://site/easydoc/documents/list.html', [
                'documents' => $documents
            ])?>

            <? // Pagination  ?>
            <? if (parameters()->total) : ?>
                <?= helper('paginator.pagination', [
                    'show_limit' => (bool) option('show_document_sort_limit')
                ]) ?>
            <? endif; ?>

        </form>
    <? endif; ?>
</div>
