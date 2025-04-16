<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<ktml:script src="assets://easydoc/site/js/items.js" />

<?= helper('behavior.modal'); ?>

<?= helper('ui.load'); ?>
<?= helper('behavior.opengraph', array(
    'entity' => $category
)) ?>

<? // RSS feed ?>
<link href="<?=route('format=rss');?>" rel="alternate" type="application/rss+xml" title="RSS 2.0" />

<? if (option('track_downloads')): ?>
    <?= helper('behavior.download_tracker'); ?>
<? endif; ?>

<? if (option('show_breadcrumb')): ?>
    <?= helper('breadcrumb.load', ['entity' => $category]) ?>
<? endif ?>

<div class="easydoc_table_layout easydoc_table_layout--default">

    <? // Toolbar ?>
    <ktml:toolbar type="actionbar">

    <? // Category ?>
    <? if ((option('show_icon') && $category->icon)
    || (option('show_category_title'))
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
                    <?= import('com://site/easydoc/document/icon.html', ['icon' => $category->icon, 'class' => ' k-icon--size-medium']) ?>
                </span>
            <? endif ?>

            <? // Header title ?>
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
                        <? if (option('show_category_owner_label', 1) && !$category->isNew() && object('user')->getId() == $category->created_by): ?>
                            <span class="label label-info"><?= translate('Owner'); ?></span>
                        <? endif; ?>
                    </span>
                </span>
            </span>
        </h3>
        <? endif; ?>

        <? if (!$category->isNew()): ?>
            <? // Edit area | Import partial template from category view ?>
            <?= import('com://site/easydoc/category/manage.html', ['category' => $category, 'bind_listener' => true]) ?>
        <? endif ?>

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

    <? // Tables ?>
    <form action="<?= url() ?>" method="get" class="k-js-grid-controller koowa_table_list">

        <? // Category table ?>
        <? if (option('show_subcategories') && count($subcategories)): ?>

            <? // Category header ?>
            <? if ($category->id && option('show_categories_header')): ?>
                <h3 class="koowa_header koowa_header--bottom_margin">
                    <?= translate('Categories') ?>
                </h3>
            <? endif; ?>

            <? // Table ?>
            <table class="table table-striped koowa_table koowa_table--categories">
                <tbody>
                    <? foreach ($subcategories as $subcategory): ?>
                    <tr>
                        <td>
                            <span class="koowa_header">
                                <? if (option('show_icon') && $subcategory->icon): ?>
                                <span class="koowa_header__item koowa_header__item--image_container">
                                    <a class="iconImage" href="<?= route($subcategory) ?>">
                                        <?= import('com://site/easydoc/document/icon.html', ['icon' => $subcategory->icon, 'class' => 'k-icon--size-default']) ?>
                                    </a>
                                </span>
                                <? endif ?>

                                <span class="koowa_header__item">
                                    <span class="koowa_wrapped_content">
                                        <span class="whitespace_preserver">
                                            <a href="<?= route($subcategory) ?>">
                                                <?= escape($subcategory->title) ?>
                                            </a>
                                            <? if (isset($subcategory->_documents_count)): ?>
                                                <span>&nbsp;(<?= $subcategory->_documents_count ?>)</span>
                                            <? endif ?>

                                            <? // Label locked ?>
                                            <? if ($subcategory->canEdit() && $subcategory->isLockable() && $subcategory->isLocked()): ?>
                                                <span class="label label-warning"><?= translate('Locked'); ?></span>
                                            <? endif; ?>

                                            <? // Label status ?>
                                            <? if (!$subcategory->enabled): ?>
                                                <span class="label label-draft"><?= translate('Draft'); ?></span>
                                            <? endif; ?>

                                            <? // Label owner ?>
                                            <? if (option('show_category_owner_label', 1) && object('user')->getId() == $subcategory->created_by): ?>
                                                <span class="label label-info"><?= translate('Owner'); ?></span>
                                            <? endif; ?>
                                        </span>
                                    </span>
                                </span>
                            </span>
                        </td>

                        <? // Edit area | Import partial template from category view ?>
                        <?= import('com://site/easydoc/category/manage.html', ['category' => $subcategory, 'redirect' => 'self', 'parent' => 'td', 'parentClass' => 'k-no-wrap', 'bind_listener' => false]) ?>
                    </tr>
                    <? endforeach; ?>
                </tbody>
            </table>
        <? endif; ?>

        <? // Documents table | Import child template from documents view ?>
        <? if (count($documents)): ?>
            <?= import('com://site/easydoc/documents/table.html', [
                'can_delete_document' => $category->canDeleteDocument(),
                'can_download'        => $category->canDownloadDocument(),
                'can_upload'          => $category->canUpload(),
                'show_action_buttons' => true
            ]) ?>
        <? endif; ?>

        <? // Pagination ?>
        <? if ($total): ?>
            <?= helper('paginator.pagination', [
                'show_limit' => (bool) option('show_document_sort_limit')
            ]) ?>
        <? endif; ?>

    </form>
</div>
