<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die;

$multi_download = $document->canDownload() && option('allow_multi_download') && object('com://site/easydoc.controller.behavior.compressible')->isSupported();
$show_action_buttons = $show_action_buttons ?? true;

?>

<?= helper('ui.load'); ?>
<?= helper('com://site/easydoc.behavior.modal'); ?>
<?= helper('com://site/easydoc.behavior.thumbnail_modal'); ?>

<? if ($multi_download): ?>
    <?= helper('behavior.multidownload'); ?>
    <?= helper('behavior.multiselect', [
        'selector' => '.koowa_media__item__options__select input'
    ]); ?>
<? endif; ?>


<? if (option('track_downloads')): ?>
    <?= helper('com://site/easydoc.behavior.download_tracker'); ?>
<? endif; ?>

<?= helper('translator.script', ['strings' => [
    'Download'
]]) ?>

<meta itemprop="contentUrl" content="<?= $document->image_download_path ?>">

<? if ($document->isPreviewableImage()): ?>

    <? if ($document->storage->width): ?>
        <meta itemprop="width" content="<?= $document->storage->width; ?>">
    <? endif; ?>
    <? if ($document->storage->height): ?>
        <meta itemprop="height" content="<?= $document->storage->height; ?>">
    <? endif; ?>
    <!-- <meta itemprop="contentUrl" content="<?= $document->image_download_path ?>"> -->
    <a class="koowa_media__item__link <?= ($document->canDownload() && !option('force_download')) ? 'k-js-gallery-item' : '' ?> <?= (option('document_title_link') === 'download') ? 'easydoc_track_download' : '' ?>"
        <?= option('document_title_link') === 'download' ? 'type="'.$document->mimetype.'"' : ''; ?>
        data-path="<?= $document->image_path ?>"
        data-title="<?= escape($document->title); ?>"
        data-id="<?= $document->id; ?>"
        data-width="<?= $document->storage->width; ?>"
        data-height="<?= $document->storage->height; ?>"
        <? if ($document->canDownload()): ?>
            href="<?= $document->title_link ?>"
        <? endif ?>
       title="<?= escape($document->title) ?>">

<? else: ?>

    <a class="koowa_media__item__link <?= option('document_title_link') === 'download' ? 'easydoc_track_download' : ''; ?>"
        <?= option('download_in_blank_page') ? 'target="_blank"' : ''; ?>
        <?= option('document_title_link') === 'download' ? 'type="'.$document->mimetype.'"' : ''; ?>
        data-title="<?= escape($document->title); ?>"
        data-id="<?= $document->id; ?>"
        <? if ($document->canDownload() || !object('user')->isAuthentic()): ?>
            href="<?= $document->title_link ?>"
        <? endif ?>
        title="<?= escape($document->title) ?>">

<? endif ?>

        <div class="koowa_media__item__content-holder">
            <? if( $document->image_path ): ?>
                <div class="koowa_media__item__thumbnail">
                    <img itemprop="thumbnail" src="<?= $document->image_path ?>" alt="<?= escape($document->title) ?>">
                </div>
            <? else: ?>
                <div class="koowa_media__item__icon">
                    <?= import('com://site/easydoc/document/icon.html', [
                        'icon'  => $document->icon,
                        'class' => ' k-icon--size-xlarge'.(strlen($document->extension) ? ' k-icon-type-'.$document->extension : '')
                    ]); ?>
                </div>
            <? endif; ?>

            <? // Label status ?>
            <? if (!$document->enabled || $document->status !== 'published'): ?>
                <? $status = $document->enabled ? translate($document->status) : translate('Draft'); ?>
                <span class="label label-<?= $document->enabled ? $document->status : 'draft' ?>"><?= ucfirst($status); ?></span>
            <? endif; ?>

            <? // Label owner ?>
            <? if (option('show_document_owner_label', 1) && object('user')->getId() == $document->created_by): ?>
                <span class="label label-info"><?= translate('Owner'); ?></span>
            <? endif; ?>

            <? // Label new ?>
            <? if (option('show_document_recent') && isRecent($document)): ?>
                <span class="label label-success"><?= translate('New'); ?></span>
            <? endif; ?>

            <? // Label popular ?>
            <? if (option('show_document_popular') && ($document->hits >= option('hits_for_popular'))): ?>
                <span class="label label-warning"><?= translate('Popular') ?></span>
            <? endif; ?>

            <? if (option('show_document_title')): ?>
                <div class="koowa_header koowa_media__item__label">
                    <div class="koowa_header__item koowa_header__item--title_container">
                        <div class="koowa_wrapped_content">
                            <div class="whitespace_preserver">
                                <div class="overflow_container">
                                    <?= escape($document->title) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <? endif; ?>
        </div>
    </a>

<? if ($show_action_buttons && ($multi_download ||  (!($document->isLockable() && $document->isLocked()) && ($document->canDelete() || $document->canEdit())))): ?>

    <div class="koowa_media__item__options">
        <? if ($document->canDelete() || $multi_download): ?>
            <span class="koowa_media__item__options__select">
                <input name="item-select" type="checkbox"
                       title="<?= translate('Select document with title {title}', ['title' => $document->title]) ?>"
                       class="k-js-item-select<?= !($document->category->canDelete() || $multi_download) ? ' k-hidden' : ''?>"
                       data-storage-type="<?= $document->storage_type ?>"
                       data-can-download="<?= (int) $document->canDownload() ?>"
                       data-can-delete="<?= (int) $document->canDelete() ?>"
                       data-id="<?= $document->id ?>"/>
            </span>
        <? endif ?>

        <? if ($document->canDelete()): ?>
            <a href="#" data-url="<?= route($document, ['endpoint' => '~documents', 'format' => 'json']) ?>" title="<?= translate('Delete document with title {title}', ['title' => $document->title]) ?>" data-params=<?= \EasyDocLabs\WP::wp_json_encode(['_method' => 'delete']) ?> data-prompt="<?= translate('Deleted items will be lost forever. Would you like to continue?') ?>" data-action="delete-item" class="koowa_media__item__options__delete">
                <span class="k-icon-trash k-icon--size-default"></span></a>
        <? endif; ?>

        <? if ($document->canEdit()): ?>
            <a href="<?= route($document, 'layout=form'. (isset($query_options) ? sprintf('&options=%s', $query_options) : '')) ?>" title="<?= translate('Edit document with title {title}', ['title' => $document->title]) ?>" class="koowa_media__item__options__edit">
                <span class="k-icon-pencil k-icon--size-default"></span>
            </a>
        <? endif ?>
    </div>

<? endif; ?>
