<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<? // Categories ?>
<div class="easydoc_categories">
    <? foreach ($categories as $category): ?>

    <? // Category ?>
    <div class="easydoc_category easydoc_category--style">

        <? // Header ?>
        <h4 class="koowa_header">

            <? // Header image ?>
            <? if (option('show_icon') && $category->icon): ?>
                <span class="koowa_header__item koowa_header__item--image_container">
                    <? // Link ?>
                    <a class="koowa_header__link" href="<?= route($category) ?>">
                        <?= import('com://site/easydoc/document/icon.html', ['icon' => $category->icon, 'class' => ' k-icon--size-medium']) ?>
                    </a>
                </span>
            <? endif ?>

            <? // Header title ?>
            <span class="koowa_header__item">
                <span class="koowa_wrapped_content">
                    <span class="whitespace_preserver">
                        <a class="koowa_header__link" href="<?= route($category) ?>">
                            <?= escape($category->title) ?>
                        </a>
                        <? if (isset($category->_documents_count)): ?>
                            <span>&nbsp;(<?= $category->_documents_count ?>)</span>
                        <? endif ?>

                        <? // Label locked ?>
                        <? if ($category->canEdit() && $category->isLockable() && $category->isLocked()): ?>
                            <span class="label label-warning"><?= translate('Locked'); ?></span>
                        <? endif; ?>

                        <? // Label status ?>
                        <? if (!$category->enabled): ?>
                            <span class="label label-draft"><?= translate('Draft'); ?></span>
                        <? endif; ?>

                        <? // Label owner ?>
                        <? if (option('show_catery_owner_label') && object('user')->getId() == $category->created_by): ?>
                            <span class="label label-info"><?= translate('Owner'); ?></span>
                        <? endif; ?>
                    </span>
                </span>
            </span>
        </h4>

        <? // Edit area | Import partial template from category view ?>
        <?= import('com://site/easydoc/category/manage.html', ['category' => $category, 'redirect' => 'self', 'bind_listener' => false]) ?>

        <? if (option('show_image') && $category->image): ?>
            <?= helper('behavior.thumbnail_modal'); ?>
            <a class="easydoc_thumbnail thumbnail" href="<?= $category->image_path ?>">
                <img src="<?= $category->image_path ?>" alt="<?= escape($category->title) ?>" />
            </a>
        <? endif ?>

        <? // Category description summary ?>
        <? if (option('show_description') && $category->description_summary): ?>
        <div class="easydoc_description">
            <?= prepareText($category->description_summary); ?>
        </div>
        <? endif ?>
	</div>
    <? endforeach; ?>
</div>
