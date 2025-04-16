<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<?= helper('ui.load'); ?>
<?= helper('behavior.jquery'); ?>
<?= helper('behavior.modal'); ?>
<?= helper('gallery.load', ['params' => options()]); ?>

<? if ($photoswipe): ?>
    <?= helper('behavior.photoswipe'); ?>
<? endif ?>

<? // RSS feed ?>
<link href="<?=route('format=rss');?>" rel="alternate" type="application/rss+xml" title="RSS 2.0" />

<? if (isset($category)): ?>

    <? if (option('show_breadcrumb')): ?>
        <?= helper('breadcrumb.load', ['entity' => $category]) ?>
    <? endif ?>

    <?= helper('behavior.opengraph', array(
        'entity' => $category
    )) ?>
<? endif; ?>

<div itemprop="mainContentOfPage" itemscope itemtype="http://schema.org/ImageGallery">

    <? // Toolbar ?>
    <ktml:toolbar type="actionbar">

    <? // Category ?>
    <? if (isset($category) &&
        ((option('show_category_title') && $category->title)
        || (option('show_image') && $category->image)
        || ($category->description_full && option('show_description')))
    ): ?>
    <div class="easydoc_category">

        <? // Header ?>
        <? if (option('show_category_title') && $category->title): ?>
        <h3 class="koowa_header">
            <? // Header image ?>
            <? if (option('show_icon') && $category->icon): ?>
            <span class="koowa_header__item koowa_header__item--image_container">
                <?= import('com://site/easydoc/document/icon.html', ['icon' => $category->icon == 'folder' ? 'image' : $category->icon, 'class' => 'k-icon--size-default']) ?>
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
                        <? if (option('show_category_owner_label', 1) && !$category->isNew() && object('user')->getId() == $category->created_by): ?>
                            <span class="label label-info"><?= translate('Owner'); ?></span>
                        <? endif; ?>
                    </span>
                </span>
            </span>
            <? endif; ?>
        </h3>
        <? endif; ?>

        <? // Edit area | Import partial template from category view ?>
        <?= import('com://site/easydoc/category/manage.html', ['category' => $category, 'bind_listener' => true]) ?>

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

    <? if( count($subcategories) || count($documents) ): ?>

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

            <div class="koowa_media--gallery">
                <div class="koowa_media_wrapper koowa_media_wrapper--categories">
                    <div class="koowa_media_contents">
                        <?php // this comment below must stay ?>
                        <div class="koowa_media"><!--
                            <? foreach($subcategories as $category): ?>
                         --><div class="koowa_media__item">
                                <div class="koowa_media__item__content">
                                    <?= import('com://site/easydoc/category/gallery.html', ['category' => $category]) ?>
                                    <?= import('com://site/easydoc/category/manage.html', ['category' => $category, 'redirect' => 'self', 'parent' => 'div', 'parentClass' => 'koowa_media__item__options', 'bind_listener' => false]) ?>

                                </div>
                            </div><!--
                            <? endforeach ?>
                     --></div>
                    </div>
                </div>
                <div class="koowa_media_wrapper koowa_media_wrapper--documents">
                    <div class="koowa_media_contents">
                        <?php // this comment below must stay ?>
                        <div class="koowa_media"><!--
                            <? $count = 0; ?>
                            <? foreach ($documents as $document): ?>
                         --><div class="koowa_media__item" itemscope itemtype="http://schema.org/ImageObject">
                                <div class="koowa_media__item__content document">
                                    <?= import('com://site/easydoc/document/gallery.html', [
                                        'document' => $document,
                                        'count'    => $count
                                    ]) ?>
                                </div>
                            </div><!--
                            <? $count++; ?>
                            <? endforeach ?>
                     --></div>
                    </div>
                </div>
            </div>

            <? // Pagination ?>
            <? if (option('show_pagination') !== false && parameters()->total): ?>
                <?= helper('paginator.pagination', [
                    'show_limit' => (bool) option('show_document_sort_limit')
                ]) ?>
            <? endif; ?>

        </form>

    <? endif; ?>
</div>
