<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<div class="k-empty-state">
    <? if(!$document_count): ?>
        <p>
            <?= translate('It seems like you don\'t have any documents yet.'); ?>
        </p>
		<? if (object('com:easydoc.model.categories')->permission('upload_document')->user(object('user')->getId())->allowed()): ?>
			<p>
				<a class="k-button k-button--success k-button--large" href="<?= route('option=com_easydoc&view=document') ?>">
					<?= translate('Add your first document')?>
				</a>
			</p>
		<? endif ?>
    <? elseif(!count($documents)) : ?>
        <p>
            <?= translate('No documents found.'); ?><br>
            <small><?= translate('Maybe select another category or different filters?'); ?></small>
        </p>
    <? endif; ?>
</div>
