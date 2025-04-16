<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

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
                    <?= helper('grid.sort', ['column' => 'title', 'title' => 'Title', 'direction' => 'asc']) ?>
                </th>
                <th width="5%" data-hide="phone,tablet,desktop">
                    <span class="k-icon-documents" aria-hidden="true"></span>
                    <span class="k-visually-hidden"><?= translate('Document count')?></span>
                </th>
                <th width="5%" data-hide="phone,tablet,desktop">
                    <?= translate('Access')?>
                </th>
                <th width="5%" data-hide="phone,tablet,desktop">
                    <?= translate('Owner')?>
                </th>
                <th width="5%" data-hide="phone,tablet,desktop">
                    <?= helper('grid.sort', ['column' => 'created_on', 'title' => 'Date']); ?>
                </th>
                <th width="1%" data-hide="phone,tablet,desktop">
                    <?= translate('ID'); ?>
                </th>
            </tr>
            </thead>
            <tbody data-behavior="orderable" data-params='{"nested":true}'>
            <? foreach($categories as $category): ?>
                <tr
                    data-level="<?= $category->level ?>"
                    data-item="<?= $category->id ?>"
                    data-parent="<?= $category->getParentId() ?>"
                    data-parents="<?= implode(' ', $category->getParentIds()) ?>"
                    data-ordering="<?= $category->ordering ?>"
                >
                    <td class="k-table-data--icon">
                        <? if(parameters()->sort == 'ordering') : ?>
                            <a class="js-sort-handle">
                                <span class="k-positioner k-is-active"></span>
                            </a>
                        <? else: ?>
                            <span data-k-tooltip='{"container":".k-ui-container"}'
                                  data-original-title="<?= translate('Please order by this column first by clicking the column title') ?>">
                            <span class="k-positioner"></span>
                        </span>
                        <? endif; ?>
                    </td>
                    <td class="k-table-data--form">
                        <?= helper('grid.checkbox', [
                            'entity'  => $category,
                            'attribs' => [
                                'data-permissions'     => \EasyDocLabs\WP::wp_json_encode([
                                    'add'    => $category->canAdd(),
                                    'edit'   => $category->canEdit(),
                                    'delete' => $category->canDelete()
                                ]),
                                'data-documents-count' => $category->_documents_count
                            ]
                        ]); ?>
                    </td>
                    <td class="k-table-data--toggle"></td>
                    <td class="k-table-data--icon">
                        <? if (substr($category->icon, 0, 5) === 'icon:'): ?>
                            <img class="k-image-16" src="icon://<?= substr($category->icon, 5) ?>"/>
                        <? else: ?>
                            <span class="k-icon-document-<?= $category->icon; ?>" aria-hidden="true"></span>
                        <? endif ?>
                    </td>
                    <td width="90%" class="k-table-data--ellipsis k-table__item-level k-table__item-level<?= $category->level;?>">
                        <? if ($category->canEdit()): ?>
                        <a data-k-tooltip='{"container":".k-ui-container","delay":{"show":500,"hide":50}}' data-original-title="<?= translate('Edit {title}', ['title' => escape($category->title)]); ?>" href="<?= route('view=category&id='.$category->id)?>">
                            <?= escape($category->title) ?>
                        </a>
                        <? else: ?>
                            <?= escape($category->title) ?>
                        <? endif ?>
                    </td>
                    <td>
                        <a data-k-tooltip='{"container":".k-ui-container","delay":{"show":500,"hide":50}}' data-original-title="<?= translate('View documents in this category') ?>" href="<?= route('view=documents&page=easydoc-documents&category='.$category->id)?>">
                            <?= $category->_documents_count; ?>
                        </a>
                    </td>
                    <td>
                        <?= is_null($category->access) ? helper('grid.access', ['entity' => $category]) : translate('Inherited') ?>
                    </td>
                    <td>
                        <div class="k-ellipsis" style="max-width: 150px;">
                            <?= escape($category->getAuthor()->getName()); ?>
                        </div>
                    </td>
                    <td class="k-table-data--nowrap">
                        <?= helper('grid.state', ['entity' => $category]) ?>
                        <small>
                            <?= helper('date.format', ['date' => $category->created_on, 'format' => 'd M Y']); ?>
                        </small>
                    </td>
                    <td>
                        <?= $category->id ?>
                    </td>
                </tr>
            <? endforeach ?>
            </tbody>
        </table>
    </div><!-- .k-table -->

    <? if (count($categories)): ?>
        <div class="k-table-pagination">
            <?= helper('paginator.pagination') ?>
        </div><!-- .k-table-pagination -->
    <? endif; ?>

</div><!-- .k-table-container -->
