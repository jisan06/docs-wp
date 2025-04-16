<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<?= helper('ui.load'); ?>

<div class="easydoc_list_layout easydoc_list_layout--tree">
    <div class="easydoc_list__sidebar">
        <div class="k-tree k-js-category-tree">
            <div class="k-sidebar-item__content k-sidebar-item__content--horizontal">
                <?= translate('Loading') ?>
            </div>
        </div>
    </div>
    <div class="easydoc_list__content">
        <ktml:content>
    </div>
</div>

<?= helper('behavior.category_tree_site', [
    'element' => '.k-js-category-tree',
    'selected' => $selected,
    'state' => $state
]) ?>

<?= helper('behavior.sidebar', [
    'sidebar'   => '#documents-sidebar',
    'target'    => '.k-js-category-tree',
    'affix'     => false,
    'minHeight' => 100
]) ?>

