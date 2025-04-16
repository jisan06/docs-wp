<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<? // Only show breadcrumbs when there's a parent element ?>
<? if (isset($parent)): ?>
<!-- Breadcrumbs -->
<div class="k-breadcrumb">
    <ul>
        <li class="k-breadcrumb__home">
            <a class="k-breadcrumb__content" href="<?= route('parent_id='); ?>">
                <span class="k-icon-home" aria-hidden="true"></span>
                <span class="k-visually-hidden"><?= translate('Home'); ?></span>
            </a>
        </li>
        <? $ancestors = $parent->getAncestors() ?? [] ?>
        <? foreach ($ancestors as $breadcrumb): ?>
        <li>
            <a class="k-breadcrumb__content" href="<?= route('parent_id='.$breadcrumb->id); ?>">
                <?= $breadcrumb->title; ?>
            </a>
        </li>
        <? endforeach; ?>
        <li class="k-breadcrumb__active">
            <span class="k-breadcrumb__content"><?= $parent->title; ?></span>
        </li>
    </ul>
</div><!-- .k-breadcrumb -->
<? endif ?>
