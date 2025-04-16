<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<!-- Breadcrumbs -->
<? // Only show breadcrumbs when a category is selected ?>
<? if ($hiearchy): ?>
<ol class="k-breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
    <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
        <a href="<?= route('', ['root' => true]); ?>" itemprop="item">
            <span class="k-icon-home" aria-hidden="true" itemprop="name"></span><span><?= $page_category ? $page_category->title : translate('Home'); ?></span>
        </a>
        <meta itemprop="position" content="1" />
    </li>
    <? foreach ($hiearchy as $item_count => $breadcrumb): ?>
        »
        <? if ($breadcrumb->breadcrumb_route): ?>
        <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
            <a href="<?= $breadcrumb->breadcrumb_route; ?>" itemprop="item">
                <span itemprop="name"><?= $breadcrumb->title; ?></span>
            </a>
            <meta itemprop="position" content="<?= $item_count ?>" />
        </li>
        <? else: ?>
        <li><span><?= $breadcrumb->title; ?></span></li>
        <? endif; ?>
    <? endforeach; ?>
</ol><!-- .k-breadcrumb -->
<? endif ?>
