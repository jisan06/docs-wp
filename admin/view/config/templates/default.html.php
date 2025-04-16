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


<?= helper('behavior.keepalive'); ?>
<?= helper('behavior.validator'); ?>
<?= helper('behavior.modal'); ?>


<? // Setting up 'translations' to be used in JavaScript ?>
<?= helper('translator.script', array('strings' => array(
    'Folder names can only contain letters, numbers, dash, underscore or colons',
    'Audio files',
    'Archive files',
    'Documents',
    'Images',
    'Video files',
    'Add another extension...'
))); ?>


<? // Loading JavaScript ?>
<ktml:script src="assets://easydoc/js/jquery.tagsinput.js" />
<ktml:script src="assets://easydoc/admin/js/config.default.js" />


<!-- Wrapper -->
<div class="k-wrapper k-js-wrapper">

    <!-- Overview -->
    <div class="k-content-wrapper">

        <!-- Content -->
        <div class="k-content k-js-content">

            <!-- Toolbar -->
            <ktml:toolbar type="actionbar">

            <!-- Component wrapper -->
            <div class="k-component-wrapper">

                <!-- Component -->
                <form class="k-component k-js-component k-js-form-controller" action="" method="post">

                    <!-- Container -->
                    <div class="k-container">

                        <!-- Main information -->
                        <div class="k-container__main">

                            <fieldset>

                                <div class="k-form-group">
                                    <label><?= translate('Use human readable titles for created categories and documents') ?></label>
                                    <div><?= helper('select.booleanlist', array('name' => 'automatic_humanized_titles', 'selected' => $config->automatic_humanized_titles)); ?></div>
                                    <p class="k-form-info">(document-2013-07-08.pdf &raquo; Document 2013 07 08)</p>
                                </div>

                            </fieldset>

                            <fieldset>

                                <legend><?= translate('Automatic monitoring'); ?></legend>
                                <div class="k-well">
                                    <?= translate('Automatic monitoring explanation'); ?>
                                </div>
                                <div class="k-form-group">
                                    <label><?= translate('Automatically create a category for new folders') ?></label>
                                    <div><?= helper('select.booleanlist', array('name' => 'automatic_category_creation', 'selected' => $config->automatic_category_creation)); ?></div>
                                    <p class="k-form-info"><?=translate('The new category will inherit settings such as permissions and owner from the parent folder category')?></p>
                                </div>

                                <div class="k-form-group">
                                    <label><?= translate('Automatically create a document for uploaded files') ?></label>
                                    <div><?= helper('select.booleanlist', array('name' => 'automatic_document_creation', 'selected' => $config->automatic_document_creation)); ?></div>
                                    <p class="k-form-info"><?=translate('The new document will inherit settings such as permissions and owner from the folder category')?></p>
                                </div>

                            </fieldset>

                            <fieldset>

                                <legend><?= translate('File extensions & permissions'); ?></legend>

                                <div class="k-tabs-container">
                                    <div class="k-tabs-wrapper">
                                        <ul class="k-tabs">
                                            <li class="k-is-active">
                                                <a href="#extensions" data-k-toggle="tab"><?= translate('Allowed file extensions') ?></a>
                                            </li>
                                            <li>
                                                <a href="#permissions" data-k-toggle="tab"><?= translate('Global permissions') ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="k-tabs-content">
                                        <div id="extensions" class="k-tab k-is-active">
                                            <div style="display: none"  class="k-inline-form-group k-js-extension-preset">
                                                <p class="k-static-form-label k-js-extension-preset-label"></p>
                                                <div class="k-button-group">
                                                    <button type="button" class="k-js-add k-button k-button--default k-button--tiny">
                                                        <span class="k-icon-plus" aria-hidden="true"></span>
                                                        <span class="k-visually-hidden"><?= translate('Plus icon') ?></span>
                                                    </button>
                                                    <button type="button" class="k-js-remove k-button k-button--default k-button--tiny">
                                                        <span class="k-icon-minus" aria-hidden="true"></span>
                                                        <span class="k-visually-hidden"><?= translate('Minus icon') ?></span>
                                                    </button>
                                                </div>
                                            </div><!-- .k-inline-form-group -->

                                            <div class="k-form-group">
                                                <label for="allowed_extensions_tag"><?= translate('Select from presets'); ?></label>
                                                <div id="extension_groups" class="k-js-extension-groups extension-groups"></div>
                                            </div>

                                            <div class="k-form-group">
                                                <input type="text" class="k-form-control" name="allowed_extensions" id="allowed_extensions"
                                                       value="<?= implode(',', EasyDocLabs\Library\ObjectConfig::unbox($config->allowed_extensions)); ?>"
                                                       data-filetypes="<?= htmlentities(\EasyDocLabs\WP::wp_json_encode($filetypes)); ?>" />
                                            </div>

                                        </div>

                                        <div id="permissions" class="k-tab">
                                            <?= import('permissions.html') ?>
                                        </div>

                                    </div>
                                </div>

                            </fieldset>

                        </div><!-- .k-container__main -->

                        <!-- Other information -->
                        <div class="k-container__sub">

                            <fieldset class="k-form-block">

                                <div class="k-form-block__header">
                                    <?= translate('License');?>
                                </div>

                                <div class="k-form-block__content">

                                    <div class="k-form-group">
                                        <div class="license-form">
                                            <? if ($license_error): ?>
                                                <p class="k-alert--danger">Invalid license: <?= $license_error ?></p>
                                            <? endif; ?>
                                            <? if ($has_connect): ?>
                                                <p class="k-alert--success"><?= translate('EasyDocs License is active') ?></p>
                                            <? endif; ?>
                                            <input
                                                type="text"
                                                id="easydocs_license_key"
                                                name="easydocs_license_key"
                                                value="<?php echo $license; ?>"
                                            >
                                            <p>
                                                <a class="k-button k-button--default k-js-refresh-license">
                                                    <?= translate('Active License')?>
                                                </a>
                                            </p>
                                        </div>
                                    </div>

                            </fieldset>
                            <fieldset class="k-form-block">

                                <div class="k-form-block__header">
                                    <?= translate('Maintenance');?>
                                </div>

                                <div class="k-form-block__content">

                                    <div class="k-form-group">
                                        <p>
                                            <a class="k-button k-button--default" href="<?= route('view=export') ?>">
                                                <?= translate('Export EasyDocs data')?>
                                            </a>
                                            <br><span class="k-form-info"><?= translate('Export EasyDocs data for backup or site migration.'); ?></span>
                                        </p>

                                        <p>
                                            <a class="k-button k-button--default" href="<?= route('view=import') ?>">
                                                <?= translate('Import from ZIP file')?>
                                            </a>
                                            <br><span class="k-form-info"><?= translate('Import a EasyDocs export ZIP file'); ?></span>
                                        </p>

                                        <p>
                                            <a class="k-button k-button--default k-js-clear-cache" >
                                                <?= translate('Clear cache')?>
                                            </a>
                                            <br><span class="k-form-info"><?= translate('Clear temporary data'); ?></span>
                                        </p>
                                    </div>

                            </fieldset>

                            <fieldset class="k-form-block">

                                <div class="k-form-block__header">
                                    <?= translate('Developer');?>
                                </div>

                                <div class="k-form-block__content">

                                    <div class="k-form-group">
                                        <label><?= translate('Developer mode'); ?></label>

                                        <?= helper('select.booleanlist', [
                                            'name' => 'debug',
                                            'selected' => $config->debug_folio,
                                        ]); ?>

                                    </div>

                                    <div class="k-form-group">

                                        <label><?= translate('Translator mode'); ?></label>

                                        <?= helper('select.booleanlist', [
                                            'name' => 'debug_lang',
                                            'selected' => $config->debug_lang,
                                        ]); ?>
                                    </div>

                                    <div class="k-form-group">

                                        <label><?= translate('Dark mode'); ?></label>


                                        <?= helper('listbox.optionlist', [
                                            'name' => 'dark_mode',
                                            'selected' => $config->dark_mode,
                                            'options' => [
                                                ['value' => '0', 'label' => translate('System default')],
                                                ['value' => '1', 'label' => translate('Always')],
                                                ['value' => '-1', 'label' => translate('Never')],
                                            ]
                                        ]); ?>
                                    </div>
                                </div>

                            </fieldset>

                        </div><!-- .k-container__sub -->

                    </div><!-- .k-container -->

                </form><!-- .k-component -->

            </div><!-- .k-component-wrapper -->

        </div><!-- .k-content -->

    </div><!-- .k-content-wrapper -->

</div><!-- .k-wrapper -->
