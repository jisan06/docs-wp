<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<?= helper('ui.load'); ?>

<a class="koowa_media__item__link" href="<?= route($category, 'layout=gallery') ?>">
    <div class="koowa_media__item__content-holder">
        <div class="koowa_header koowa_media__item__label">
            <div class="koowa_header__item koowa_header__item--image_container">
                <? if ($category->image_path): ?>
                    <img itemprop="thumbnail" src="<?= $category->image_path ?>"
                         alt="<?= escape($category->title) ?>">
                <? else: ?>
                    <?= import('com://site/easydoc/document/icon.html', [
                        'icon'  => $category->icon,
                        'class' => 'k-icon--size-medium'
                    ]); ?>
                <? endif ?>
            </div>
            <div class="koowa_header__item">
                <div class="koowa_wrapped_content">
                    <div class="whitespace_preserver">
                        <div class="overflow_container">
                            <?= escape($category->title) ?>
                            <? if (isset($category->_documents_count)): ?>
                                <span>&nbsp;(<?= $category->_documents_count ?>)</span>
                            <? endif ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</a>