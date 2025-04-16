<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<? // Loading necessary Markup, CSS and JS ?>
<?= helper('ui.load', ['package' => 'easydoc']) ?>


<ktml:content>


<ktml:script src="assets://easydoc/admin/js/files.select.js" />


<script>
window.addEvent('domready', function(){
	kQuery('#insert-document').click(function(e) {
		e.preventDefault();

        <? if (!empty($callback)): ?>
        window.parent.<?= $callback; ?>(Files.app.selected);
        <? endif; ?>
	});
});
</script>


<p id="document-insert-form" style="display: none;">
	<button class="k-button k-button--success k-button--block" type="button" id="insert-document" disabled><?= translate('Insert') ?></button>
</p>
