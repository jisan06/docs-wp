<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<div class="k-empty-state">
    <? if(!$tag_count) : ?>
        <p>
            <?= translate('It seems like you don\'t have any tags yet.'); ?>
        </p>
        <p>
            <a class="k-button k-button--large k-button--success" href="<?= route('option=com_easydoc&view=tag') ?>">
                <?= translate('Add your first tag')?>
            </a>
        </p>
    <? elseif(!count($tags)) : ?>
        <p>
            <?= translate('No tags found.'); ?><br>
            <small><?= translate('Maybe select different filters?'); ?></small>
        </p>
    <? endif; ?>
</div>
