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
                <th width="1%" class="k-table-data--form">
                    <?= helper('grid.checkall')?>
                </th>
                <th width="1%" class="k-table-data--toggle" data-toggle="true"></th>
                <th>
                    <?= helper('grid.sort', ['column' => 'name', 'title' => 'Name']); ?>
                </th>
                <th width="5%" data-hide="phone,tablet,desktop">
                    <?= helper('grid.sort', ['column' => 'count', 'title' => '<span class="k-icon-people" aria-hidden="true"></span><span class="k-visually-hidden">'.translate('Users count').'</span>', 'url' => route()]); ?>
                </th>
                <th width="5%" data-hide="phone,tablet">
                    <?= helper('grid.sort', ['column' => 'created_on', 'title' => 'Date']); ?>
                </th>
            </tr>
            </thead>
            <tbody>
            <? foreach($usergroups as $usergroup): ?>
                <tr>
                    <td class="k-table-data--form">
                        <?= helper('grid.checkbox', ['entity' => $usergroup])?>
                    </td>
                    <td class="k-table-data--toggle"></td>
                    <td class="k-table-data--ellipsis">
                        <a data-k-tooltip='{"container":".k-ui-container","delay":{"show":500,"hide":50}}' data-original-title="<?= translate('Edit {title}', ['title' => escape($usergroup->name)]); ?>" href="<?= route('view=usergroup&id='.$usergroup->id); ?>">
                            <?= escape($usergroup->name); ?>
                        </a>
                    </td>
                    <td>
                        <?= escape($usergroup->getUsers()->count()); ?>
                    </td>
                    <td class="k-table-data--nowrap">
                        <?= helper('date.format', ['date' => $usergroup->created_on, 'format' => 'd M Y']); ?>
                    </td>
                </tr>
            <? endforeach ?>
            </tbody>
        </table>

    </div><!-- .k-table -->

    <? if (count($usergroup)): ?>
        <div class="k-table-pagination">
            <?= helper('paginator.pagination') ?>
        </div><!-- .k-table-pagination -->
    <? endif; ?>

</div><!-- .k-table-container -->
