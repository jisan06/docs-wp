<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<div class="k-sidebar-left k-js-sidebar-left">

    <!-- Navigation -->
    <div class="k-sidebar-item">
        <ktml:toolbar type="menubar">
    </div>

    <!-- Category tree -->
    <div class="k-sidebar-item">
        <div class="k-sidebar-item__header">
            <?= translate('Categories'); ?>
        </div>

        <? if ($category_count): ?>
        <div class="k-tree k-js-category-tree">
            <?= helper('behavior.category_tree', [
                'selected' => parameters()->parent_id,
                'options'  => ['state' => 'parent_id'],
                'state'    => ['sort' => parameters()->sort, 'access' => $access]
            ]); ?>
        </div><!-- k-tree -->
        <? else : ?>
        <div class="k-sidebar-item__content">
            <?= translate('No categories found')?>
        </div>
        <? endif; ?>
    </div>

    <!-- Filters -->
    <div class="k-sidebar-item k-js-sidebar-toggle-item">
        <div class="k-sidebar-item__header">
            <?= translate('Quick filters'); ?>
        </div>
        <ul class="k-list">
            <li class="<?= parameters()->created_by == object('user')->getId() ? 'k-is-active' : ''; ?>">
                <a href="<?= route('parent_id=&created_by='.(parameters()->created_by == 0 ? object('user')->getId() : '')) ?>">
                    <span class="k-icon-person" aria-hidden="true"></span>
                    <?= translate('My Categories') ?>
                </a>
            </li>
        </ul>
    </div>

</div><!-- .k-sidebar-left -->
