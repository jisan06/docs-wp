<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die;

$can_manage_one = false;

if (!isset($can_upload)) $can_upload = false;

if (!isset($can_download)) $can_download = false;

if (!isset($can_delete_document)) $can_delete_document = false;

foreach ($documents as $document)
{
    if (!$show_action_buttons || $can_manage_one) break;

    if ($document->canEdit() || $document->canDelete()) {
        $can_manage_one = true;
    }
}

$multi_download = $can_download && option('allow_multi_download') && object('com://site/easydoc.controller.behavior.compressible')->isSupported();

?>

<ktml:script src="assets://easydoc/js/footable.js" />

<? if ($multi_download): ?>
    <?= helper('behavior.multidownload'); ?>
    <?= helper('behavior.multiselect'); ?>
<? endif; ?>

<script>
kQuery(function($) {
    $('.k-js-documents-table').footable({
        toggleSelector: '.footable-toggle',
        breakpoints: {
            phone: 400,
            tablet: 600,
            desktop: 800
        }
    }).bind('footable_row_detail_updated', function(event) {
        var container = event.detail;

        container.find('.btn-mini').addClass('btn-small').removeClass('btn-mini');

        container.find('.footable-row-detail-value').css('display', 'inline-block');

        container.find('.footable-row-detail-name').css('display', 'inline-block')
            .each(function() {
                var $this = $(this);

                if ($.trim($this.text()) == '') {
                    $this.remove();
                }
            });
    });

});
</script>

<?= helper('behavior.downloadlabel', ['params' => options()]); ?>

<? if (option('track_downloads')): ?>
    <?= helper('behavior.download_tracker'); ?>
<? endif; ?>

<? if ($can_upload): ?>
    <?= helper('behavior.modal'); ?>
<? endif; ?>

<? // Documents header & sorting ?>
<div class="easydoc_block">
    <? if (option('show_documents_header')): ?>
        <h3 class="koowa_header">
            <?= translate('Documents')?>
        </h3>
    <? endif; ?>
</div>

<? // Sorting ?>
<? if (option('show_document_sort_limit') && count($documents)): ?>
    <div class="easydoc_sorting form-search">
        <label for="sort-documents" class="control-label"><?= translate('Order by') ?></label>
        <?= helper('paginator.sort_documents', [
            'sort'      => 'document_sort',
            'direction' => 'document_direction',
            'attribs'   => ['class' => 'input-medium', 'id' => 'sort-documents']
        ]); ?>
    </div>
<? endif; ?>

<? // Table ?>
<table class="table table-striped koowa_table koowa_table--documents k-js-documents-table">
    <thead style="display: none">
		<tr>
			<? if ($can_delete_document || $multi_download): ?>
				<th data-hide="phone"><?= translate('Select'); ?></th>
			<? endif ?>
				<th width="1%" data-toggle="true" class="k-table-data--toggle"><?= translate('Toggle'); ?></th>
				<th><?= translate('Title'); ?></th>
			<? if (option('show_document_created')): ?>
				<th data-hide="phone"><?= translate('Date'); ?></th>
			<? endif; ?>
			<? if (option('document_title_link') !== 'download'): ?>
				<th data-hide="phone,tablet"><?= translate('Download'); ?></th>
			<? endif; ?>
			<? if ($can_manage_one): ?>
				<th data-hide="phone,tablet"><?= translate('Manage'); ?></th>
			<? endif ?>
		</tr>
    </thead>
    <tbody>
    <? foreach ($documents as $document): ?>
        <? $document->can_download =  $document->canDownload() ?>
        <tr class="easydoc_item" data-document="<?= $document->uuid ?>" itemscope itemtype="http://schema.org/CreativeWork">
            <? if ($show_action_buttons && ($can_delete_document || $multi_download)): ?>
            <td width="1%">
                <input name="item-select" class="k-js-item-select" type="checkbox"
                       title="<?= translate('Select document with title {title}', ['title' => $document->title]) ?>"
                       data-id="<?= $document->id ?>"
                       data-url="<?= route($document, 'endpoint=~documents&format=json') ?>"
                       data-storage-type="<?= $document->storage_type ?>"
                       data-can-download="<?= (int) $document->can_download ?>"
                       data-can-delete="<?= (int) $document->canDelete() ?>"
                />
            </td>
            <? endif; ?>
            <td width="1%" class="k-table-data--toggle"></td>
            <? // Title and labels ?>
            <td>
                <meta itemprop="contentUrl" content="<?= $document->image_download_path ?>">
                <span class="koowa_header">
                    <? // Icon ?>
                    <? if ($document->icon && option('show_document_icon')): ?>
                        <span class="koowa_header__item koowa_header__item--image_container">
                            <? if (option('document_title_link') && ($document->can_download || !object('user')->isAuthentic())): ?>
                                <a href="<?= ($document->title_link) ?>"
                                <?= option('document_title_link') === 'download' ? 'type="'.$document->mimetype.'"' : ''; ?>
                                <?= option('download_in_blank_page') && option('document_title_link') === 'download'  ? 'target="_blank"' : ''; ?>
                                >
                            <? endif; ?>

                            <?= import('com://site/easydoc/document/icon.html', [
                                'icon'  => $document->icon,
                                'class' => ' k-icon--size-default'.strlen($document->extension) ? ' k-icon-type-'.$document->extension : ' k-icon-type-remote'
                            ]) ?>

                            <? if (option('document_title_link')): ?>
                                </a>
                            <? endif; ?>
                        </span>
                    <? endif ?>

                    <? // Title ?>
                    <span class="koowa_header__item">
                        <span class="koowa_wrapped_content">
                            <span class="whitespace_preserver">
                                <? if (option('document_title_link') && ($document->can_download || !object('user')->isAuthentic())): ?>
                                    <a href="<?= $document->title_link ?>" title="<?= escape($document->storage->name);?>"
                                       <?= option('document_title_link') === 'download' ? 'type="'.$document->mimetype.'"' : ''; ?>
                                       class="<?= option('document_title_link') === 'download' ? 'easydoc_track_download' : ''; ?>"
                                       data-title="<?= escape($document->title); ?>"
                                       data-id="<?= $document->id; ?>"
                                        <?= option('download_in_blank_page') && option('document_title_link') === 'download'  ? 'target="_blank"' : ''; ?>
                                    ><span itemprop="name"><?= escape($document->title);?></span><!--
                                        --><? if ($document->title_link === $document->download_link): ?>
                                            <? // Filetype and Filesize  ?>
                                            <? if ((option('show_document_size') && $document->size) || ($document->storage_type == 'file' && option('show_document_extension'))): ?>
                                                <span class="easydoc_download__info">(
                                                    <? if ($document->storage_type == 'file' && option('show_document_extension')): ?>
                                                        <?= escape($document->extension . (option('show_document_size') && $document->size ? ', ':'')) ?>
                                                    <? endif ?>
                                                    <? if (option('show_document_size') && $document->size): ?>
                                                        <?= helper('string.humanize_filesize', ['size' => $document->size]) ?>
                                                    <? endif ?>
                                                )</span>
                                            <? endif; ?>
                                        <? endif ?><!--
                                    --></a>
                                <? else: ?>
                                    <span title="<?= escape($document->storage->name);?>">
                                        <span itemprop="name"><?= escape($document->title);?></span>
                                        <? if ($document->title_link === $document->download_link
                                            && (option('show_document_size') && $document->size || $document->storage_type == 'file' && option('show_document_extension'))): ?>
                                            (<?= $document->extension ? $document->extension.', ' : '' ?><?= helper('string.humanize_filesize', ['size' => $document->size]); ?>)
                                        <? endif; ?>
                                    </span>
                                <? endif; ?>

                                <? // Document hits ?>
                                <? if (option('show_document_hits') && $document->hits): ?>
                                    <meta itemprop="interactionCount" content=”UserDownloads:<?= $document->hits ?>">
                                    <span class="detail-label">(<?= object('translator')->choose(['{number} download', '{number} downloads'], $document->hits, ['number' => $document->hits]) ?>)</span>
                                <? endif; ?>

                                <? // Label locked ?>
                                <? if ($document->canEdit() && $document->isLockable() && $document->isLocked()): ?>
                                    <span class="label label-warning"><?= translate('Locked'); ?></span>
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
                                    <span class="label label-success"><?= translate('New') ?></span>
                                <? endif; ?>

                                <? // Label popular ?>
                                <? if (option('show_document_popular') && ($document->hits >= option('hits_for_popular'))): ?>
                                    <span class="label label-warning"><?= translate('Popular') ?></span>
                                <? endif; ?>
                            </span>
                        </span>
                    </span>
                </span>
            </td>

            <? // Date ?>
            <? if (option('show_document_created')): ?>
            <td width="1%" class="koowa_table__dates">
                <time itemprop="datePublished"
                      datetime="<?= parameters()->sort === 'touched_on' ? $document->touched_on : $document->publish_date ?>"
                >
                    <?= helper('date.format', [
                        'date' => parameters()->sort === 'touched_on' ? $document->touched_on : $document->publish_date,
                        'format' => 'd M Y']); ?>
                </time>
            </td>
            <? endif; ?>

            <? // Download ?>
            <? if (option('document_title_link') !== 'download'): ?>
            <td width="1%" class="koowa_table__download k-no-wrap">
                <? //hide download for audio/video ?>
                <? $can_show_player = !option('force_download') && option('show_player'); ?>
                <? $player = helper('player.render', ['document' => $document]) ?>
                <? if (!$can_show_player || empty($player)): ?>
                <a class="btn btn-default btn-mini easydoc_track_download easydoc_download__button" href="<?= $document->download_link; ?>"
                    <?= option('download_in_blank_page') ? 'target="_blank"' : ''; ?>
                    type="<?= $document->mimetype ?>"
                    data-title="<?= escape($document->title); ?>"
                    data-id="<?= $document->id; ?>"
                    <? if(!option('force_download')): ?>
                    data-mimetype="<?= $document->mimetype ?>"
                    data-extension="<?= $document->extension ?>"
                    <? endif; ?>
                    >
                    <span class="easydoc_download_label">
                        <?= translate('Download'); ?>
                    </span>

                    <? // Filetype and Filesize  ?>
                    <? if ((option('show_document_size') && $document->size) || ($document->storage_type == 'file' && option('show_document_extension'))): ?>
                        <span class="easydoc_download__info easydoc_download__info--inline">(<!--
                            --><? if ($document->storage_type == 'file' && option('show_document_extension')): ?><!--
                                --><?= escape($document->extension . (option('show_document_size') && $document->size ? ', ':'')) ?><!--
                            --><? endif ?><!--
                            --><? if (option('show_document_size') && $document->size): ?><!--
                                --><?= helper('string.humanize_filesize', ['size' => $document->size]) ?><!--
                            --><? endif ?><!--
                            -->)</span>
                    <? endif; ?>
                </a>
                <? endif; ?>
            </td>
            <? endif ?>

            <? if ($can_manage_one): ?>
            <? // Edit buttons ?>
            <td class="koowa_table__manage">
                <? // Manage | Import partial template from document view ?>
                <?= import('com://site/easydoc/document/manage.html', [
                    'document' => $document,
                    'button_size' => 'mini'
                    ]) ?>
            </td>
            <? endif ?>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
