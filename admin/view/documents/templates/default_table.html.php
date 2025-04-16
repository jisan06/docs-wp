<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<ktml:style src="assets://easydoc/css/tooltips.css"/>
<ktml:script src="assets://easydoc/js/clipboard.min.js" />

<script>
    EasyDoc.ready(() =>
    {
        const permalinks = document.querySelectorAll('.k-easydoc-permalink');

        for (const permalink of permalinks) {
            new EasyDocClipboard({selector: permalink, tooltip: {direction: 'w', message: <?= \EasyDocLabs\WP::wp_json_encode(translate('Copy permalink')) ?>}});
        }
    });
</script>

<div class="k-table-container">
    <div class="k-table">

        <table class="k-js-responsive-table">
            <thead>
            <tr>
                <th width="1%" class="k-table-data--icon" data-ignore="true" data-hide="phone, tablet">
                    <?= helper('grid.sort', ['column' => 'ordering', 'title' => '<span class="k-icon-move"></span>', 'direction' => 'desc']) ?>
                </th>
                <th width="1%" class="k-table-data--form">
                    <?= helper('grid.checkall')?>
                </th>
                <th width="1%" class="k-table-data--toggle" data-toggle="true"></th>
                <th width="1%" class="k-table-data--icon"></th>
                <th>
                    <?= helper('grid.sort', ['column' => 'title', 'title' => 'Title']); ?>
                </th>
                <th width="5%" data-hide="phone,tablet,desktop">
                    <?= translate('Access')?>
                </th>
                <th width="5%" data-hide="phone,tablet,desktop">
                    <?= helper('grid.sort', ['column' => 'created_by', 'title' => 'Owner']); ?>
                </th>
                <th width="5%" data-hide="phone,tablet,desktop">
                    <?= helper('grid.sort', ['column' => 'created_on', 'title' => 'Date']); ?>
                </th>
                <th width="1%" class="k-table-data--icon" data-hide="phone,tablet,desktop">
                    <?= helper('grid.sort', ['column' => 'hits', 'title' => '<span class="k-icon-cloud-download" aria-hidden="true"></span><span class="k-visually-hidden">'.translate('Download').'</span>']); ?>
                </th>
                <th width="1%" class="k-table-data--icon"></th>
                <th width="1%" class="k-table-data--icon" data-hide="phone,tablet,desktop"></th>
                </th>
                <th width="1%" data-hide="phone,tablet,desktop">
                    <?= helper('grid.sort', ['column' => 'id', 'title' => 'ID']); ?>
                </th>
            </tr>
            </thead>
            <tbody <?= parameters()->sort == 'ordering' ? 'data-behavior="orderable"' : '' ?>>
            <? $i = 1;
            foreach ($documents as $document):
                $document->isPermissible();
                $location = false;
                ?>
                <tr
                data-item="<?= $document->id ?>"
                data-ordering="<?= $document->ordering ?>"
                >
                    <td class="k-table-data--icon">
                        <div>
                            <? if(parameters()->sort == 'ordering') : ?>
                                <a class="js-sort-handle">
                                    <span class="k-positioner k-is-active"></span>
                                </a>
                            <? else: ?>
                                <span class="k-positioner"
                                      data-k-tooltip='{"container":".k-ui-container"}'
                                      data-original-title="<?= translate('Please order by this column first by clicking the column title') ?>"></span>
                          <? endif; ?>
                        </div>
                    </td>
                    <td class="k-table-data--form">
                        <?= helper('grid.checkbox', [
                            'entity'  => $document,
                            'attribs' => [
                                'data-permissions' => \EasyDocLabs\WP::wp_json_encode([
                                    'edit'   => $document->canEdit(),
                                    'delete' => $document->canDelete(),
                                    'copy'   => $document->category->canUpload()
                                ]),
                                'data-entity'      => htmlentities(\EasyDocLabs\WP::wp_json_encode($document->toArray()))
                            ]
                        ]) ?>
                    </td>
                    <td class="k-table-data--toggle"></td>
                    <td class="k-table-data--icon">
                        <? if (substr($document->icon, 0, 5) === 'icon:'): ?>
                            <span class="koowa_header__image_container">
                                <img src="icon://<?= substr($document->icon, 5) ?>" class="koowa_header__image" />
                            </span>
                        <? else: ?>
                        <span class="k-icon-document-<?= $document->icon; ?>" aria-hidden="true"></span>
                        <? endif ?>
                    </td>
                    <td class="k-table-data--ellipsis">
                        <? if (empty($attachments) && $document->canEdit()) : ?>
                        <a data-k-tooltip='{"container":".k-ui-container","delay":{"show":500,"hide":50}}' data-original-title="<?= translate('Edit {title}', ['title' => escape($document->title)]); ?>"
                           href="<?= route('view=document&id='.$document->id); ?>" >
                            <?= escape($document->title); ?>
                        </a>
                        <? else: ?>
                        <?= escape($document->title); ?>
                        <? endif ?>

                        <? if ($document->storage_type == 'remote') : ?>
                            <? $location = $document->storage_path; ?>
                        <? elseif ($document->storage_type == 'file') : ?>
                            <? $location = $document->storage_path; ?>
                            <? if ($document->size): ?>
                                <span>
                                    <? $location .= ' - '.helper('string.humanize_filesize', ['size' => $document->size]); ?>
                                </span>
                            <? endif; ?>
                        <? endif ?>
                        <? if($location) : ?>
                            <small title="<?= escape($location) ?>">
                                <?= $location ?>
                            </small>
                        <? endif ?>
                    </td>
                    <td>
                        <?= helper('grid.access', ['entity' => $document]) ?>
                    </td>
                    <td>
                        <div class="k-ellipsis" style="max-width: 150px;">
                            <?= escape($document->getAuthor()->getName()); ?>
                        </div>
                    </td>
                    <td class="k-table-data--nowrap">
                        <?= helper('grid.state', ['entity' => $document, 'clickable' => empty($attachments)]) ?>
                        <small>
                        <?= helper('date.format', ['date' => $document->created_on, 'format' => 'd M Y']); ?>
                        </small>
                    </td>
                    <td>
                        <?= $document->hits; ?>
                    </td>
                    <td class="k-table-data--icon">
						<? if ($document->canDownload()): ?>
							<? if ($document->storage_type == 'remote'): ?>
								<? $location = $document->storage_path; ?>
							<? else: ?>
								<? $location = route('view=file&routed=1&container=easydoc-files&folder='.($document->storage->folder === '.' ? '' : rawurlencode($document->storage->folder)).'&name='.rawurlencode($document->storage->name)); ?>
							<? endif ?>
							<a data-k-tooltip='{"container":".k-ui-container","delay":{"show":500,"hide":50}}' data-original-title="Download document" href="<?= $location; ?>" target="_blank">
								<span class="k-icon-data-transfer-download" aria-hidden="true"></span>
								<span class="k-visually-hidden"><?= translate('Download'); ?></span>
							</a>
						<? else: ?>
							<span class="k-icon-data-transfer-download k-icon--disabled" aria-hidden="true"></span>
						<? endif ?>
                    </td>
                    <td class="k-table-data--icon">
                        <a class="k-easydoc-permalink" data-clipboard-text="<?= escape(helper('permalink.generate', ['entity' => $document])) ?>"><span class="k-icon-clipboard""></span></a>
                    </td>
                    <td>
                        <?= $document->id ?>
                    </td>
                </tr>
            <? endforeach; ?>
            </tbody>
        </table>
    </div><!-- .k-table -->

    <? if (count($documents)): ?>
        <div class="k-table-pagination">
            <?= helper('paginator.pagination') ?>
        </div><!-- .k-table-pagination -->
    <? endif; ?>

</div><!-- .k-table-container -->

