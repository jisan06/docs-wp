<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<div class="k-empty-state">
    <? if(!$usergroups_count) : ?>
        <p>
            <?= translate('It seems like you don\'t have any usergroups yet.'); ?>
        </p>
        <p>
            <a class="k-button k-button--large k-button--success" href="<?= route('option=com_easydoc&view=usergroup') ?>">
                <?= translate('Add your first usergroup')?>
            </a>
        </p>
    <? elseif(!count($usergroups)) : ?>
        <p>
            <?= translate('No usergroups found.'); ?><br>
            <small><?= translate('Maybe select different filters?'); ?></small>
        </p>
    <? endif; ?>
</div>
