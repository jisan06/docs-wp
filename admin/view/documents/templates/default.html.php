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
<?= helper('behavior.modal') ?>
<?= helper('behavior.tooltip') ?>


<? // Loading JavaScript ?>
<ktml:script src="assets://easydoc/admin/js/toolbar.js" />
<ktml:script src="assets://easydoc/admin/js/documents.default.js"/>


<? if (parameters()->sort == 'ordering') : ?>
<ktml:script src="assets://easydoc/admin/js/jquery.sortable.js"/>
<ktml:script src="assets://easydoc/admin/js/ordering.js"/>
<? endif; ?>

<!-- Wrapper -->
<div class="k-wrapper k-js-wrapper">

    <!-- Title when sidebar is invisible -->
    <ktml:toolbar type="titlebar" title="EasyDocsubmenu documents" mobile>

    <!-- Overview -->
    <div class="k-content-wrapper">

        <!-- Sidebar -->
        <?= import('default_sidebar.html'); ?>

        <!-- Content -->
        <div class="k-content k-js-content">

            <!-- Toolbar -->
            <ktml:toolbar type="actionbar">

            <? if (!$category_count): ?>
                <script>
                    kQuery(function($) {
                        $('#toolbar-new, #toolbar-upload').off().removeClass('k-button--success').removeAttr('href').addClass('k-is-disabled');
                    });
                </script>
            <? endif; ?>

            <!-- Component wrapper -->
            <div class="k-component-wrapper">

                <!-- Component -->
                <form class="k-component k-js-component k-js-grid-controller " action="" method="get">

                    <!-- Breadcrumbs -->
                    <?= import('default_breadcrumbs.html'); ?>

                    <!-- Scopebar -->
                    <?= import('default_scopebar.html'); ?>

                    <? if (!$document_count || !count($documents)) : ?>

						<!-- Check for categories -->
						<? if (!$category_count): ?>

							<!-- No categories yet -->
							<?= import('no_categories.html'); ?>

						<? else: ?>

							<!-- No documents -->
							<?= import('no_documents.html'); ?>

						<? endif ?>

                    <? else : ?>

                        <!-- Table -->
                        <?= import('default_table.html'); ?>

                    <? endif; ?>

                </form><!-- .k-component -->

            </div><!-- .k-component-wrapper -->

        </div><!-- k-content -->

    </div><!-- .k-content-wrapper -->

</div><!-- .k-wrapper -->

<div class="k-dynamic-content-holder">
    <?= import('modal_move.html') ?>
    <?= import('modal_duplicate.html') ?>
    <?= import('modal_batch.html') ?>
</div>
