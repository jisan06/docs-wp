<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<?= helper('behavior.alpine') ?>

<ktml:script src="assets://easydoc/js/notifiable.js" />

<section x-data="EasyDoc.notifiable.getInstance(<?= escape(\EasyDocLabs\WP::wp_json_encode($notifiable_data)) ?>)">

    <div class="k-alert k-alert--warning" x-show="data.notifier && (row != data.row)">
        <?= translate('You are editing a notification that has been defined on a parent category') ?>
    </div>  

    <button x-show="show_notifiers == false" @click.prevent="show_notifiers = true" type="button" class="k-button k-button--primary k-button--block">
        <?= translate('Add a new notification') ?>
    </button>

    <div x-show="show_notifiers == true" class="k-form-group">
        <label for="easydoc-notifiers-name"><?= translate('Notifier') ?></label>
        <?= helper('notifier.notifiers', ['notifiers' => $notifiers, 'attribs' => ['id' => 'easydoc-notifiers-name']]) ?>
    </div>

    <!-- Render all notifiers specific layouts -->
    <? foreach($notifiers as $notifier): ?>

        <div x-show="data.notifier == '<?= (string) $notifier ?>'" class="k-form-group">
            <label for="easydoc-notifier-<?= $notifier->getName() ?>-actions"><?= translate('Actions') ?></label>
            <?= helper('notifier.actions', ['notifier' => $notifier, 'attribs' => ['id' => sprintf('easydoc-notifier-%s-actions', $notifier->getName())]]) ?>
        </div>

        <div x-show="data.notifier == '<?= (string) $notifier ?>'" class="k-form-group">
            <label for="easydoc-notifier-<?= $notifier->getName() ?>-description"><?= translate('Description') ?></label>
            <textarea placeholder="<?= translate('Provide a meaningful description of the notification') ?>" x-model="data.description" class="k-form-control" id="easydoc-notifier-<?= $notifier->getName() ?>-description"></textarea>
        </div>

        <?= $notifier->render(['entity' => $category]) ?>

    <? endforeach ?>

    <div class="k-form-group" style="padding-top: 7px" x-show="data.notifier && (row == data.row)">
        <label><input type="checkbox" id="easydoc-notifiers-inherit" x-model="data.inheritable" /><?= translate('Inheritable') ?></label><br>
        <span class="k-form-info"><?= translate('Set as inheritable if you would like this notifier to apply to descending categories also') ?></span>
    </div>

    <div class="k-form-group" style="padding-bottom: 7px">
        <button class="k-button k-button--primary" :class="validate() ? '' : 'k-is-disabled'" x-show="data.notifier && !data.id" @click.prevent="add()"><?= translate('Add') ?></button>
        <button class="k-button k-button--success" :class="validate() ? '' : 'k-is-disabled'" x-show="data.notifier && data.id" @click.prevent="edit()"><?= translate('Update') ?></button>
        <button class="k-button k-button--warning" x-show="data.notifier" @click.prevent="reset()"><?= translate('Reset') ?></button>
    </div>

    <div x-show="notifications.length" class="k-form-block">
        <div class="k-form-block__header"><?= translate('Registered Notifications') ?></div>
        <div class="k-form-block__content">
            <div class="k-table-container">
                <div class="k-table">
                    <table>
                        <thead>
                        <tr>
                            <th>Notifier</th>
                            <th>Action</th>
                            <th>Description</th>                      
                            <th width="1%"></th>
                            <th width="1%"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <template x-for="notification in notifications"  :key="notification.id">
                            <tr>
                                <td style="font-weight: bold" x-text="notifiers[notification.notifier]['name']"></td>
                                <td>
                                    <p>
                                        <template x-for="action in notification.parameters.actions">
                                            <span class="k-label k-label--accent" style="margin: 3px" x-text="notifiers[notification.notifier]['actions'][action]['label']"></span>
                                        </template>
                                    </p>
                                </td>
                                <td x-text="notification.description"></td>
                                <td>
                                    <button type="button" @click.prevent="fill(notification)" class="k-button k-button--success k-button--tiny">
                                        <span class="k-visually-hidden"><?= translate('Edit') ?></span>
                                        <span class="k-icon-pencil" aria-hidden="true"></span>
                                    </button>
                                </td>
                                <td>
                                    <button :class="row == notification.row ? '' : 'k-is-disabled'" type="button" @click.prevent="remove(notification.id)" class="k-button k-button--danger k-button--tiny">
                                        <span class="k-visually-hidden"><?= translate('Delete') ?></span>
                                        <span class="k-icon-trash" aria-hidden="true"></span>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>