<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<div class="k-empty-state">
    <? if(!$category_count) : ?>
        <p>
            <?= translate('It seems like you don\'t have any categories yet.'); ?>
        </p>
        <? if (object('user')->canAddCategory()): ?>
        <p>
            <a class="k-button k-button--large k-button--success" href="<?= route('option=com_easydoc&view=category') ?>">
                <?= translate('Add your first category')?>
            </a>
        </p>
        <? endif ?>
    <? else : ?>
        <p>
            <?= translate('No categories found.'); ?><br>
            <small><?= translate('Maybe select another category or different filters?'); ?></small>
        </p>
    <? endif; ?>
</div>
