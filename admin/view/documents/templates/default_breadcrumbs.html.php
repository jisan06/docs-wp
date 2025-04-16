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
<? if (isset($category)): ?>
<div class="k-breadcrumb">
    <ul>
        <li class="k-breadcrumb__home">
            <a class="k-breadcrumb__content" href="<?= route('category='); ?>">
                <span class="k-icon-home" aria-hidden="true"></span>
                <span class="k-visually-hidden"><?= translate('Home'); ?></span>
            </a>
        </li>
        <? $ancestors = $category->getAncestors() ?? [] ?>
        <? foreach ($ancestors as $breadcrumb): ?>
        <li>
            <a class="k-breadcrumb__content" href="<?= route('category='.$breadcrumb->id); ?>">
                <?= $breadcrumb->title; ?>
            </a>
        </li>
        <? endforeach; ?>
        <li class="k-breadcrumb__active">
            <span class="k-breadcrumb__content"><?= $category->title; ?></span>
        </li>
    </ul>
</div><!-- .k-breadcrumb -->
<? endif ?>
