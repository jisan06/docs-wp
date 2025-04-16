<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<?= helper('ui.load'); ?>

<ktml:script src="assets://easydoc/js/footable.js" />

<script>
kQuery(function($) {
    $('.k-js-documents-table').footable({
        toggleSelector: '.footable-toggle',
        breakpoints: {
            phone: 400,
            tablet: 600,
            desktop: 800
        }
    });
});
</script>

<? // Table ?>
<table class="table table-striped koowa_table koowa_table--documents k-js-documents-table">
    <thead style="display: none">
		<tr>
			<th data-hide="phone"></th>
			<th><?= translate('Title'); ?></th>
			<th data-hide="phone"><?= translate('Date'); ?></th>
		</tr>
    </thead>
    <tbody>
    <? foreach ($documents as $document): ?>
        <?
        $size_extension = null;
        $can_download = $document->canDownload();

        if (($document->title_link === $document->download_link) &&
			((option('show_document_extension') && $document->storage_type == 'file') || (option('show_document_size') && $document->size)))
        {
            $size_extension = '<span class="easydoc_download__info">(';

            if (option('show_document_extension') && $document->storage_type == 'file') {
                $size_extension .= escape($document->extension . (option('show_document_size') && $document->size ? ', ':''));
            }

            if (option('show_document_size') && $document->size) {
                $size_extension .= helper('string.humanize_filesize', array('size' => $document->size));
            }

			$size_extension .= ')';
        }

        $class = ' k-icon--size-default'.strlen($document->extension) ? ' k-icon-type-'.$document->extension : ' k-icon-type-remote';

        $icon = '<span class="k-icon-document-'.$document->icon.' '.$class.'" aria-hidden="true"></span><span class="k-visually-hidden"><?= translate($icon); ?></span>';

        $hits = null;
        if (option('show_document_hits') && $document->hits)
        {
            $hits = object('translator')->choose(array('{number} download', '{number} downloads'), $document->hits, array('number' => $document->hits));
            $hits = '<span class="detail-label">(' . $hits . ')</span>';
            $hits .= '<meta itemprop="interactionCount" content="UserDownloads:'.$document->hits.'">';
        }
        ?>
        <tr class="easydoc_item" data-document="<?= $document->uuid ?>" itemscope itemtype="http://schema.org/CreativeWork">
            <? // Title and labels ?>
            <td width="1%">
                <? if (option('show_icon')): ?>
                <span class="koowa_header__item koowa_header__item--image_container">
                    <?= $icon ?>
                </span>
                <? endif; ?>
            </td>
            <td>
                <h4 class="koowa_header">
                    <? // Header title ?>
                    <? if ($can_download): ?>
                        <a<?= option('download_in_blank_page') ? ' target="__blank"' : '' ?>
                            <?= option('document_title_link') === 'download' ? 'type="'.$document->mimetype.'"' : ''; ?>
                            class="koowa_header__title_link"
                            href="<?= $document->download_link ?>"
                            title="<?= escape($document->storage->name);?>">
                            <?= escape($document->title) ?>
                            <?= $size_extension ?>
                            <?= $hits ?>
                        </a>
                    <? else: ?>
                        <span>
                            <?= escape($document->title) ?>
                            <?= $size_extension ?>
                            <?= $hits ?>
                        </span>
                    <? endif ?>

                    <? // Label locked ?>
                    <? if ($document->canEdit() && $document->isLockable() && $document->isLocked()): ?>
                    <span class="label label-warning"><?= helper('com://admin/easydoc.grid.lock_message', array('entity' => $document)); ?></span>
                    <? endif; ?>
                </h4>
            </td>

            <? // Date ?>
			<? if (option('show_document_created')): ?>
            <td width="1%" class="koowa_table__dates">
                <time itemprop="datePublished"
                      datetime="<?= parameters()->sort === 'touched_on' ? $document->touched_on : $document->publish_date ?>"
                >
                    <?= helper('date.format', array(
                        'date' => parameters()->sort === 'touched_on' ? $document->touched_on : $document->publish_date,
                        'format' => 'd M Y')); ?>
                </time>
            </td>
			<? endif ?>

        </tr>
    <? endforeach ?>
    </tbody>
</table>
