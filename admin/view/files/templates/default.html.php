<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<? // Loading necessary Markup, CSS and JS ?>
<?= helper('ui.load', ['package' => 'easydoc']); ?>
<?= helper('behavior.tooltip') ?>

<? // Loading JavaScript ?>
<script data-inline src="assets://easydoc/admin/js/files.default.js" type="text/javascript"></script>


<?= import('templates_modal.html'); ?>


<? // Setting up 'translations' to be used in JavaScript ?>
<?= helper('translator.script', ['strings' => [
    'Create documents'
]]); ?>


<!-- Wrapper -->
<div class="k-wrapper k-js-wrapper">

    <!-- Title when sidebar is invisible -->
    <ktml:toolbar type="titlebar" title="EasyDocsubmenu files" mobile>

    <!-- Overview -->
    <div class="k-content-wrapper">

        <!-- Sidebar -->
        <?= import('default_sidebar.html'); ?>

        <!-- Content -->
        <div class="k-content k-js-content">

            <!-- Toolbar -->
            <ktml:toolbar type="actionbar">

            <!-- Content -->
            <ktml:content>

        </div><!-- k-content -->

    </div><!-- .k-content-wrapper -->

</div><!-- .k-wrapper -->
