<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<ktml:script src="assets://easydoc/js/permissions.js" />

<script>
    kQuery(function () {
        EasyDoc.permissions({actions: <?= \EasyDocLabs\WP::wp_json_encode(\EasyDocLabs\EasyDoc\ModelEntityPermission::getActions()) ?>, selector_template: '#default-permissions-{action}', fixed_groups: <?= \EasyDocLabs\WP::wp_json_encode(\EasyDocLabs\EasyDoc\ModelEntityUsergroup::FIXED) ?>,  default: 'public' });
    });
</script>

<div class="k-table-container">
    <div class="k-table">
        <table class="k-js-responsive-table k-permissions">
            <thead>
            <tr>
                <th class="k-permissions__header k-permissions__header--action"><?= translate('Action') ?></th>
                <th class="k-permissions__header k-permissions__header--current"><?= translate('Allowed groups') ?></th>
            </tr>
            </thead>
            <tbody>
            <? foreach ($permissions_actions as $section => $actions): ?>
                <tr class="k-table__sub-header">
                    <th colspan="7">
                        <span class="k-permissions__section"><?= translate(ucfirst($section)) ?></span>
                    </th>
                </tr>
                <? foreach ($actions as $action => $data): ?>
                    <tr>
                        <td class="k-permissions__header">
                            <?= translate(ucfirst(str_replace('_', ' ', $data['label']))) ?>
                        </td>
                        <td>
                            <div>
                                <?= helper('permission.usergroups', [
                                    'prompt'   => translate('Select groups'),
                                    'selected' => isset($permissions[$action]) ? $permissions[$action] : [],
                                    'action'   => $action,
                                    'attribs'  => array_merge(isset($data['attribs']) ? $data['attribs'] : [], ['id' => sprintf('default-permissions-%s', $action)]),
                                    'default'  => \EasyDocLabs\EasyDoc\ModelEntityUsergroup::FIXED['public'],
                                    'locked'   => $locked_groups,
                                    'name'     => sprintf('permissions[%s]', $action),
                                    'deselect' => false
                                ]) ?>
                            </div>
                        </td>
                    </tr>
                <? endforeach ?>
            <? endforeach ?>
            </tbody>
        </table>
    </div>
</div>