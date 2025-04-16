<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<?= helper('ui.load'); ?>
<?= helper('behavior.keepalive'); ?>
<?= helper('behavior.tooltip') ?>
<?= helper('behavior.validator', [
    'options' => [
        'ignore' => '',
        'messages' => [
            'storage_path_file' => ['required' => translate('This field is required')],
            'title'             => ['required' => translate('This field is required')]
        ]
    ]
]); ?>

<?= helper('translator.script', ['strings' => [
    'Please wait for the upload to finish before saving the document',
    'Your link should either start with http:// or another protocol',
    'Invalid remote link. This link type is not supported by your server.',
    'Update',
    'Upload'
]]); ?>

<ktml:script src="assets://easydoc/site/js/submit.default.js" />

<div class="easydoc_submit_layout">

    <? if ($show_form): ?>
        <? // Form ?>
        <div class="koowa_form">
            <form action="<?= isset($query_options) ? route(sprintf('options=%s', $query_options)) : ''?>" method="post" class="k-js-form-controller" enctype="multipart/form-data">
                <div class="k-ui-namespace boxed">
                    <fieldset class="form-horizontal">

                        <div class="control-group">
                            <label><?= translate('File') ?></label>
                            <input type="hidden" id="storage_path_file" required value="" />

                            <?= helper('com:files.uploader.container', [
                                'container' => 'easydoc-files',
                                'element' => '.easydoc-uploader',
                                'attributes' => [
                                    'style' => 'margin-bottom: 0'
                                ],
                                'options'   => [
                                    'check_duplicates' => false,
                                    'multi_selection' => false,
                                    'autostart' => false,
                                    'url' => route('view=file&plupload=1&routed=1&format=json', false, false)
                                ]
                            ]); ?>
                        </div>


                        <div class="control-group submit_document__title_field">
                            <label for="title_field"><?= translate('Title'); ?></label>
                            <input required
                                   class="input input-block-level"
                                   id="title_field"
                                   type="text"
                                   name="title"
                                   maxlength="255"
                                   placeholder="<?= translate('Title') ?>"
                                   value="<?= escape($document->title); ?>" />
                        </div>

                        <? if ($show_categories): ?>
                            <div class="control-group submit_document__category_field">
                                <label><?= translate('Category') ?></label>
                                <?= helper('listbox.categories', [
                                    'name'        => 'easydoc_category_id',
                                    'permissions' => 'upload_document',
                                    'deselect'    => false,
                                    'filter'      => [
                                        'parent_id'    => $categories,
                                        'include_self' => true,
                                        'level'        => $level,
                                        'access'       => object('user')->getRoles(),
                                        'current_user' => object('user')->getId(),
                                        'enabled'      => true
                                    ]]) ?>
                            </div>
                        <? else: ?>
                            <input type="hidden" name="easydoc_category_id" value="<?= $categories ?>">
                        <? endif ?>

                        <? if (option('show_description', 0)): ?>
                        <div class="control-group submit_document__description_field">
                            <label><?= translate('Summary'); ?></label>
                            <textarea rows="20" cols="200" style="width: 100%; height: 200px" name="description"></textarea>
                        </div>
                        <? endif ?>
                    </fieldset>


                </div>

                <input type="hidden" name="automatic_thumbnail" value="1" />
                <input type="hidden" name="redirect" value="<?= url() ?>" />
            </form>
        </div>
    <? else: ?>
        <h3><?= translate('You do not have enough privileges to upload new documents') ?></h3>
    <? endif ?>

    <!-- Toolbar -->
    <ktml:toolbar type="actionbar">

</div>
