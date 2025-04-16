<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<? if (!isset($navigation)) $navigation = true ?>

<div class="k-sidebar-left k-js-sidebar-left">

    <!-- Navigation -->
    <? if ($navigation === true): ?>
    <div class="k-sidebar-item">
        <ktml:toolbar type="menubar">
    </div>
    <? endif ?>

    <!-- Categories -->
    <div class="k-sidebar-item">
        <div class="k-sidebar-item__header">
            <?= translate('Categories'); ?>
        </div>
        <? if ($category_count): ?>
            <div class="k-tree k-js-category-tree">

                <? $config = [
                    'selected' => parameters()->category,
                    'state'    => [
                        'sort'             => 'title',
                        'access'           => $access,
                        'documents_count'  => true,
                        'documents_access' => true
                    ],
                    'route'    => ['view' => 'documents']
                ] ?>

                <? if (isset($layout)): ?>
                    <? $config['route']['layout'] = $layout ?>
                <? endif ?>

                <?= helper('behavior.category_tree', $config); ?>
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
            <?= translate('Quick filters') ?>
        </div>
        <ul class="k-list">
            <? $user_id = object('user')->getId(); ?>
            <li class="<?= parameters()->created_by == $user_id ? 'k-is-active' : ''; ?>">
                <a href="<?= route('category=&created_by='.(parameters()->created_by == 0 || parameters()->created_by != $user_id ? $user_id : '')) ?>">
                    <span class="k-icon-person" aria-hidden="true"></span>
                    <?= translate('My Documents') ?>
                </a>
            </li>
            <li class="<?= parameters()->sort === 'created_on' && parameters()->direction === 'desc' ? 'k-is-active' : ''; ?>">
                <a href="<?= route(parameters()->sort === 'created_on' && parameters()->direction === 'desc' ? 'category=&sort=&direction=&created_by=' : 'category=&sort=created_on&direction=desc&created_by=') ?>">
                    <span class="k-icon-clock" aria-hidden="true"></span>
                    <?= translate('Recently Added') ?>
                </a>
            </li>
            <li class="<?= parameters()->sort === 'modified_on' && parameters()->direction === 'desc' ? 'k-is-active' : ''; ?>">
                <a href="<?= route(parameters()->sort === 'modified_on' && parameters()->direction === 'desc' ? 'category=&sort=&direction=&created_by=' : 'category=&sort=modified_on&direction=desc&created_by=') ?>">
                    <span class="k-icon-pencil" aria-hidden="true"></span>
                    <?= translate('Recently Edited') ?>
                </a>
            </li>
            <li class="<?= parameters()->sort === 'hits' && parameters()->direction === 'desc' ? 'k-is-active' : ''; ?>">
                <a href="<?= route(parameters()->sort === 'hits' && parameters()->direction === 'desc' ? 'category=&sort=&direction=&created_by=' : 'category=&sort=hits&direction=desc&created_by=') ?>">
                    <span class="k-icon-star" aria-hidden="true"></span>
                    <?= translate('Most Popular') ?>
                </a>
            </li>
        </ul>
    </div>

</div><!-- .k-sidebar-left -->
