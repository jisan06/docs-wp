<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die;

// str_replace helps convert the paths before the template filter transform media:// to full path
$options = str_replace('\/', '/', \EasyDocLabs\WP::wp_json_encode($config['options']));
?>

<?= helper('behavior.modal'); ?>

<ktml:script src="assets://easydoc/js/image.editor.js" />
<ktml:script src="assets://easydoc/js/thumbnail.js" />

<script>
    kQuery(function($){
        new ThumbnailBox({
            el: '.k-js-thumbnail',
            store: $('.k-js-form-controller').data('controller').store,
            data: <?= $options ?>
        });
    });
</script>


<div class="k-js-thumbnail">
    <div class="k-form-group">
        <div class="k-optionlist">
            <div class="k-optionlist__content">
                <input type="radio" v-model="active" value="none" id="thumbnailpicker0" />
                <label for="thumbnailpicker0"><?= translate('None'); ?></label>

                <input type="radio" v-model="active" value="custom" id="thumbnailpicker1" />
                <label for="thumbnailpicker1"><?= translate('Upload'); ?></label>

                <input v-show="hasWebSupport" v-bind:disabled="hasConnectSupport ? false : true"
                       type="radio" v-model="active" value="web" id="thumbnailpicker3" />
                <label v-show="hasWebSupport" for="thumbnailpicker3"><?= translate('Web'); ?></label>

                <input v-show="automatic.enabled" v-bind:disabled="hasAutomaticSupport ? false : true" type="radio" v-model="active" value="automatic" id="thumbnailpicker2" />
                <label v-show="automatic.enabled" for="thumbnailpicker2" ><?= translate('Generate'); ?></label>

                <div class="k-faux-focus"></div>
            </div>
        </div>
    </div>

    <div>
        <p v-show="download_in_progress && download_in_progress_error" class="k-alert k-alert--warning">
            <?= translate('Please wait while the image is downloaded'); ?>
        </p>
        <p v-show="entity.storage_path && isLocal && !hasAutomaticSupport" class="k-alert k-alert--info">
            <?= translate('Automatically generated thumbnails are not supported on this file type.'); ?>
        </p>
        <p v-show="isRemote" class="k-alert k-alert--info">
            <?= translate('Automatically generated thumbnails are only supported on local files.'); ?>
        </p>
    </div>

    <div class="thumbnail-preview">
        <div class="k-card k-card--rounded" style="width: 132px;">
            <div class="k-card__body">
                <div class="k-card__section k-card__section--small-spacing">
                    <div class="k-ratio-block k-ratio-block--1-to-1">
                        <div class="k-ratio-block__body">
                            <div class="k-ratio-block__centered">
                                <div v-show="!preview_url"><span class="k-icon-document-image k-icon--size-medium" aria-hidden="true"></span></div>
                                <img v-bind:src="preview_url" v-show="preview_url" alt="" />
                                <span class="k-loader k-loader--medium" v-show="download_in_progress"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="k-card__footer" v-show="active === 'automatic' && automatic.exists">
                    <button v-bind:disabled="!hasConnectSupport" v-on:click.prevent="editImage"
                            class="k-button k-button--block k-button--default k-button--small" type="button"><?= translate('Edit'); ?></button>

                </div>
                <div class="k-card__footer" v-show="active === 'custom'">
                    <button v-on:click.prevent="changeCustom" class="k-button k-button--block k-button--default k-button--small" type="button"><?= translate('Change'); ?></button>
                    <button v-bind:disabled="!hasConnectSupport" v-on:click.prevent="editImage"
                            class="k-button k-button--block k-button--default k-button--small" type="button"><?= translate('Edit'); ?></button>

                </div>
                <div class="k-card__footer" v-show="active === 'web'">
                    <button v-bind:disabled="!hasConnectSupport" v-on:click.prevent="openPicker" class="k-button k-button--block k-button--default k-button--small" type="button"><?= translate('Change'); ?></button>
                    <button v-bind:disabled="!hasConnectSupport" v-on:click.prevent="editImage" class="k-button k-button--block k-button--default k-button--small" type="button"><?= translate('Edit'); ?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="k-dynamic-content-holder">
        <input type="hidden" name="image" value="" v-if="active == 'none'" />
        <input type="hidden" name="image" v-bind:value="entity.image"
               v-if="active == 'custom' || active == 'web' || active == 'automatic'" />
        <input type="hidden" name="automatic_thumbnail" value="1" v-if="active == 'automatic'" />
    </div>
</div>

<div class="k-js-image-uploader-container" style="display: none">
    <?= helper('com:files.uploader.container', [
        'container' => 'easydoc-images',
        'element' => '.image-uploader',
        'options'   => [
            'drop_element' => 'element',
            'multipart_params' => ['overwrite' => 1],
            'duplicate_mode' => 'overwrite',
            'check_duplicates' => false,
            'multi_selection' => false,
            'autostart' => true,
            'url' => route('view=file&plupload=1&routed=1&format=json', false, false)
        ]
    ]); ?>
</div>
