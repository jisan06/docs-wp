<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<div class="k-empty-state">
    <p><?= translate('It seems like you don\'t have any categories yet.'); ?></p>
	<? if (object('com:easydoc.model.categories')->permission('add_category')->user(object('user')->getId())->allowed()): ?>
		<p>
			<a class="k-button k-button--success k-button--large" href="<?= route('option=com_easydoc&view=category') ?>">
				<?= translate('Add your first category')?>
			</a>
		</p>
	<? endif ?>
</div>
