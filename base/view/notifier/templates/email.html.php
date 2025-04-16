<?
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */
defined('FOLIOKIT') or die; ?>

<script>
    const easydocNotifiableEmail = component => {

        let notifier = component.notifiers['<?= (string) $notifier ?>'];
        const iframe = document.querySelector('#easydoc-notifier-<?= $notifier->getName() ?>-body').contentWindow;
        const origin = window.location.origin;

        notifier.name = '<?= $notifier->getName() ?>';

        window.addEventListener('message', function(event)
        {
            if (event.origin !== origin || !event.data.publisher || event.data.publisher !== 'easy-docs-ait/editor/notifier-email-body') {
                return;
            }

            if (event.data.event === 'content-change')
            {
                component.data.parameters.body = event.data.content;
                notifier.body_edited = true;
            }
        });

        component.registerHandlers('<?= (string) $notifier ?>', {
            validate: function ()
            {
                let has_groups = this.data.parameters.groups == null ? 0 : this.data.parameters.groups.length;
                let has_users = this.data.parameters.users == null ? 0 : this.data.parameters.users.length;
                let has_actions = this.data.parameters.actions == null ? false : !!this.data.parameters.actions.length;

                return this.data.notifier && has_actions && (has_users || has_groups);
            },
            onSelect: function ()
            {
                if (!component.data.id)
                {
                    // Only set default body when adding a new notifier (on fill, data.id is set)

                    iframe.postMessage({
                        event: 'set-content',
                        content: notifier.body.generic
                    }, origin);

                    notifier.body_edited = false;
                    component.data.parameters.body = notifier.body.generic;
                }
            },
            fill: function(notification)
            {
                // Sync both users and groups selectors

                if (notification.parameters.groups) {
                    kQuery("#easydoc-notifier-email-goups").val(notification.parameters.groups).trigger("change");
                }

                if (notification.parameters.users) {
                    kQuery("#easydoc-notifier-email-users").val(notification.parameters.users).trigger("change");;
                }

                const payload = {
                    publisher: 'easy-docs-ait/editor',
                };

                const origin = window.location.origin;

                iframe.postMessage({
                    event: 'set-content',
                    content: notification.parameters.body
                }, origin);

                notifier.body_edited = true; // Set body as edited
            },
            setActions: function(actions)
            {
                if (!(notifier.body_edited || component.data.id))
                {
                    let type = 'generic';

                    if (actions)
                    {
                        if (!Array.isArray(actions)) actions = [actions];

                        for (const action of actions)
                        {
                            const parts = action.split('_');

                            if (type == 'generic') type = parts[0];

                            if (type != parts[0])
                            {
                                type = 'generic';
                                break;
                            }
                        }

                        if (actions.length === 1)
                        {
                            let action = actions[0];

                            const parts = action.split('_');

                            type = parts[0];
                        }
                    }

                    iframe.postMessage({
                        event: 'set-content',
                        content: notifier.body[type]
                    }, origin);
                }
            }
        });
    };
</script>

<section x-show="data.notifier == '<?= (string) $notifier ?>'" x-init="easydocNotifiableEmail(component)">
    <div class="k-form-group">
        <label for="easydoc-notifier-<?= $notifier->getName() ?>-groups"><?= translate('Groups') ?></label>
        <?= helper('notifier.groups', [
            'attribs' => [
                'id' => sprintf('easydoc-notifier-%s-goups', $notifier->getName())
            ]
        ]) ?>
    </div>

    <div class="k-form-group">
        <label for="easydoc-notifier-<?= $notifier->getName() ?>-users"><?= translate('Users') ?></label>
        <?= helper('notifier.users', [
            'selected' => $notifier->getUsers($entity),
            'attribs' => [
                'id' => sprintf('easydoc-notifier-%s-users', $notifier->getName())
            ]
        ]) ?>
    </div>

    <div class="k-form-group">
        <label for="easydoc-notifier-<?= $notifier->getName() ?>-subject"><?= translate('Subject') ?></label>
        <input x-model="data.parameters.subject" class="k-form-control" type="text" id="easydoc-notifier-<?= $notifier->getName() ?>-subject" />
    </div>

    <div class="k-form-group">
        <label for="easydoc-notifier-<?= $notifier->getName() ?>-body"><?= translate('Body') ?></label>
        <iframe class="k-form-control" id="easydoc-notifier-<?= $notifier->getName() ?>-body" src="<?= route('view=editor&layout=default&field=notifier-email-body'); ?>" style="width: 100%; min-height: 400px; border: 0"></iframe>
    </div>
</section>