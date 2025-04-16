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
<ktml:script src="assets://easydoc/admin/js/attachments.js" />

<script>
    kQuery(function($)
    {
        var category = <?= \EasyDocLabs\WP::wp_json_encode(parameters()->category) ?>;

        var payload = {
            publisher: 'easy-docs-ait/attachments',
            event: 'selected-documents',
            selected: []
        };

        var uploader = $('.attachments-uploader');

        uploader.on('uploader:selected', function(event, data) {
            payload.selected = [];
        });

        uploader.on('uploader:complete', function(event, data)
        {
            var state = data.uploader.total;

            var loop = setInterval(function()
            {
                if (payload.selected.length === state.uploaded)
                {
                    window.parent.postMessage(payload, '*');
                    clearInterval(loop);
                }
            }, 1000);
        });

        uploader.on('uploader:uploaded', function (event, data)
        {
            var file = data.file;

            if (file.status === plupload.DONE)
            {
                $.ajax({
                    url: <?= \EasyDocLabs\WP::wp_json_encode(route('view=document&format=json')) ?>,
                    type: 'POST',
                    dataType: 'json',
                    data:
                    {
                        storage_type: 'file',
                        storage_path: 'tmp/' + file.name,
                        humanize_titles: 1,
                        title: file.name,
                        created_by: <?= \EasyDocLabs\WP::wp_json_encode(object('user')->getId()) ?>,
                        _action: 'add',
                        easydoc_category_id: category
                    },
                    error: function() {
                        uploader.uploader('notify', 'error', <?= \EasyDocLabs\WP::wp_json_encode(translate('There was an error while creating documents')) ?>);
                    },
                    success: function (result) {
                        payload.selected.push(result.data.attributes);
                    }
                });
            }
        });

        $('.k-alert__close').click(function() {
            $(this).closest('.k-container').hide();
        });
    });
</script>

<!-- Wrapper -->
<div class="k-wrapper k-js-wrapper">

    <!-- Title when sidebar is invisible -->
    <ktml:toolbar type="titlebar" title="EasyDocsubmenu documents" mobile>

    <!-- Overview -->
    <div class="k-content-wrapper">

        <!-- Sidebar -->
        <?= import('default_sidebar.html', ['navigation' => false, 'layout' => 'attachments']); ?>

        <!-- Content -->
        <div class="k-content k-js-content">

            <!-- Component wrapper -->
            <div class="k-component-wrapper">

                <div class="k-component k-js-component">

                    <div class="k-container">

                        <div class="k-container__full">

                            <? if (isset($category)): ?>

                                <?= helper('com:files.uploader.container', [
                                    'container' => 'easydoc-files',
                                    'element' => '.attachments-uploader',
                                    'options'   => [
                                        'multi_selection' => true,
                                        'multipart_params' => [
                                            'folder' => 'tmp',
                                            'overwrite' => 1
                                        ],
                                        'autostart' => true,
                                        'url' => route('view=file&plupload=1&routed=1&format=json')
                                    ]
                                ]); ?>

                            <? else: ?>

                                <div class="k-alert k-alert--info">
                                    <button type="button" class="k-alert__close k-js-alert-close" title="Close" aria-label="Close"><?= translate('Close') ?></button>
                                    <?= translate('You need to select a category on the sidebar to create new documents') ?>
                                </div>

                            <? endif ?>



                        </div><!-- .k-container__full -->

                    </div>

                    <div class="k-container">

                        <!-- Component -->
                        <form class="k-js-grid-controller" action="" method="get">

                            <!-- Breadcrumbs -->
                            <?= import('default_breadcrumbs.html'); ?>

                            <!-- Scopebar -->
                            <?= import('default_scopebar.html'); ?>

                            <!-- Table -->
                            <?= import('default_table.html', ['attachments' => true]); ?>

                        </form><!-- .k-component -->

                    </div>

                </div>

            </div><!-- .k-component-wrapper -->

            <div class="k-container">
                <div class="k-align-right">

                    <!-- Toolbar -->
                    <ktml:toolbar type="actionbar">

                </div>
            </div>



        </div><!-- k-content -->

    </div><!-- .k-content-wrapper -->

</div><!-- .k-wrapper -->

