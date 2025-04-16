<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<? // Loading necessary Markup, CSS and JS ?>
<?= helper('ui.load') ?>
<?= helper('behavior.tooltip') ?>


<? // Setting up 'translations' to be used in JavaScript ?>
<?= helper('translator.script', ['strings' => [
    'You cannot delete a category while it still has documents'
]]); ?>

<? // Loading JavaScript ?>
<ktml:script src="assets://easydoc/admin/js/toolbar.js" />
<ktml:script src="assets://easydoc/admin/js/categories.default.js" />

<? if (parameters()->sort == 'ordering') : ?>
<ktml:script src="assets://easydoc/admin/js/jquery.sortable.js"/>
<ktml:script src="assets://easydoc/admin/js/ordering.js"/>
<? endif; ?>

<!-- Wrapper -->
<div class="k-wrapper k-js-wrapper">

    <!-- Title when sidebar is invisible -->
    <ktml:toolbar type="titlebar" title="EasyDocsubmenu categories" mobile>

    <!-- Content wrapper -->
    <div class="k-content-wrapper">

        <!-- Sidebar -->
        <?= import('default_sidebar.html'); ?>

        <!-- Content -->
        <div class="k-content k-js-content">

            <!-- Toolbar -->
            <ktml:toolbar type="actionbar">

            <!-- Component wrapper -->
            <div class="k-component-wrapper">

                <!-- Component -->
                <form class="k-component k-js-component k-js-grid-controller" action="" method="get">

                    <!-- Scopebar -->
                    <?= import('default_breadcrumbs.html'); ?>

                    <!-- Scopebar -->
                    <?= import('default_scopebar.html'); ?>

                    <!-- Check for categories -->
                    <? if(!$category_count || !count($categories)) : ?>

                        <!-- No categories -->
                        <?= import('no_categories.html'); ?>

                    <? else : ?>

                        <!-- Table -->
                        <?= import('default_table.html'); ?>

                    <? endif; ?>

                </form><!-- .k-component -->

            </div><!-- .k-component-wrapper -->

        </div><!-- k-content -->

    </div><!-- .k-content-wrapper -->

</div><!-- .k-wrapper -->
