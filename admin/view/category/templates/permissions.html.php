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
    kQuery(function ($)
    {
        easydoc_usergroups_permissions = EasyDoc.permissions({
            actions: <?= \EasyDocLabs\WP::wp_json_encode(\EasyDocLabs\EasyDoc\ModelEntityPermission::getActions()) ?>,
            fixed_groups: <?= \EasyDocLabs\WP::wp_json_encode(\EasyDocLabs\EasyDoc\ModelEntityUsergroup::FIXED) ?>
        });

        easydoc_users_permissions = EasyDoc.permissions({
            type: 'users',
            actions: <?= \EasyDocLabs\WP::wp_json_encode(\EasyDocLabs\EasyDoc\ModelEntityPermission::getActions()) ?>,
            fixed_groups: <?= \EasyDocLabs\WP::wp_json_encode(\EasyDocLabs\EasyDoc\ModelEntityUsergroup::FIXED) ?>
        });
    });
</script>

<div class="k-table-container">
    <div class="k-table">
        <table class="k-js-responsive-table k-permissions">
            <tbody>
            <? foreach ($permissions_actions as $section => $actions): ?>
                <tr class="k-table__sub-header">
                    <th colspan="4">
                        <span class="k-permissions__section"><?= translate(ucfirst($section)) ?></span>
                    </th>
                </tr>
                <? foreach ($actions as $action => $data): ?>
                    <tr>
                        <td rowspan="2" class="k-permissions__label">
                            <?= translate(ucfirst($data['label'])) ?>
                        </td>
						<td style="text-align: right;">
							<label><?= translate('Groups') ?></label>
						</td>
                        <td style="min-width: 100%;">
							<div class="<?= !empty($allowed_usergroups[$action]['current']) ? 'k-hidden' : '' ?>">
                            <?= helper('permission.usergroups', [
                                'selected'   => $allowed_usergroups[$action]['parent'],
                                'name'       => null,
                                'inherited'  => true,
                                'attribs'    => [
                                    'id'                       => sprintf('usergroups-inherited-%s', $action),
                                    'disabled'                 => true,
                                    'data-default-permissions' => (int) !$inheriting[$action]
                                ],
                                'prompt'     => ''
                            ]) ?>
							</div>
							<div style="min-width: 100%;" class="<?= empty($allowed_usergroups[$action]['current']) ? 'k-hidden' : '' ?>">
								<?= helper('permission.usergroups', [
									'prompt'   => translate('Select groups'),
									'selected' => $allowed_usergroups[$action]['current'],
									'action'   => $action,
									'locked'   => $locked[$section]['usergroups'],
									'name'     => sprintf('permissions[usergroups][%s]', $action),
									'attribs'  => array_merge(isset($data['attribs']) ? $data['attribs'] : [], ['id' => sprintf('usergroups-permissions-%s', $action)])
								]) ?>
							</div>
                        </td>
						<td>
							<button id="usergroups-<?= $action ?>-clear" class="k-button k-button--danger k-button--block<?= empty($allowed_usergroups[$action]['current']) ? ' k-hidden' : '' ?>">
                                <?= translate('Reset') ?>
                            </button>
							<button id="usergroups-<?= $action ?>-toggle" class="k-button k-button--default k-button--block<?= !empty($allowed_usergroups[$action]['current']) ? ' k-hidden' : '' ?>">
                                <?= translate('Override') ?>
                            </button>
						</td>
                    </tr>
                    <tr>
						<td style="text-align: right;">
							<label><?= translate('Users') ?></label>
						</td>
                        <td style="min-width: 100%;">
							<div class="<?= !empty($allowed_users[$action]['current']) ? 'k-hidden' : ''?>">
							<?= helper('permission.users', [
                                'selected'   => $allowed_users[$action]['parent'],
                                'name'       => null,
                                'inherited'  => true,
                                'attribs'    => [
                                    'id'       => sprintf('users-inherited-%s', $action),
                                    'disabled' => true
                                ],
                                'prompt'     => ''
                            ]) ?>
							</div>
							<div style="min-width: 100%;" class="<?= empty($allowed_users[$action]['current']) ? 'k-hidden' : ''?>">
                                <?= helper('permission.users', [
                                    'selected' => $allowed_users[$action]['current'],
                                    'action'   => $action,
                                    'prompt' => translate('Search users'),
                                    'action'   => $action,
                                    'locked'   => $locked[$section]['users'],
                                    'name'     => sprintf('permissions[users][%s]', $action),
                                    'attribs'  => array_merge(isset($data['attribs']) ? $data['attribs'] : [], ['id' => sprintf('users-permissions-%s', $action)])
                                ]) ?>
                        	</div>
                        </td>
						<td>
							<button id="users-<?= $action ?>-clear" class="k-button k-button--danger k-button--block<?= empty($allowed_users[$action]['current']) ? ' k-hidden' : '' ?>">
                                <?= translate('Reset') ?>
                            </button>
							<button id="users-<?= $action ?>-toggle" class="k-button k-button--default k-button--block<?= !empty($allowed_users[$action]['current']) ? ' k-hidden' : '' ?>">
                                <?= translate('Override') ?>
                            </button>
						</td>
                    </tr>
                <? endforeach ?>
            <? endforeach ?>
            </tbody>
        </table>
    </div>
</div>
