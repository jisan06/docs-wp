/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

var EasyDoc= EasyDoc|| {};

EasyDoc.notifiable = {

    instance: null,

    getInstance(data = null)
    {
        if (!this.instance)
        {
            this.instance = {

                initialize(data) {

                    this.show_notifiers = false;
                    this.row = data.row;
                    this.table = data.table;
                    this.notifications = data.notifications;
                    this.notifiers = data.notifiers;
                    this.url = data.url;
                    this.data_default = {
                        row: this.row,
                        table: this.table,
                        notifier: null,
                        description: null,
                        inheritable: true,
                        parameters: {
                            actions: null
                        }
                    };

                    this.debug = data.debug !== null ? data.debug : false

                    this.reset(false);
                },

                reset(selectors = true) {

                    this.data = JSON.parse(JSON.stringify(this.data_default));

                    if (selectors) {
                        kQuery('.k-select2-resettable').val(null).trigger('change');
                    }

                    this.show_notifiers = false;

                    window.dispatchEvent(new CustomEvent('notifiable:reset', {detail: this}));
                },

                fill(notification) {

                    this.data = JSON.parse(JSON.stringify(notification));

                    // Sync selectors

                    kQuery('#easydoc-notifiers-name').val(notification.notifier).trigger('change');
                    kQuery('#easydoc-notifier-' + this.current.name + '-actions').val(notification.parameters.actions).trigger('change');

                    if (typeof this.current.fill === "function") {
                        this.current.fill(notification);
                    }

                    // Fix inheritable checkbox value
                    if (typeof this.data.inheritable === 'string') {
                        this.data.inheritable = this.data.inheritable !== '0' ? true : false; 
                    }

                    this.show_notifiers = true;

                },

                validate() {
                    return this.data.notifier && this.notifiers[this.data.notifier].validate();
                },

                registerHandlers(notifier, handlers) {

                    Object.keys(handlers).forEach((name, index, array) => {
                        this.notifiers[notifier][name] = handlers[name].bind(this);
                    });

                    return this;
                },

                get current() {
                    let notifier = false;

                    if (this.data.notifier) {
                        notifier = this.notifiers[this.data.notifier];
                    }

                    return notifier;
                },

                add() {
                    let that = this;

                    fetch(this.url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json, text/plain, */*',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(this.data)
                        }
                    )
                        .then(result => result.json())
                        .then(data => this.notifications.push(data.data.attributes))
                        .then(that.reset());
                },

                remove(id) {
                    let url = this.url + `&id=${id}`,
                        that = this;

                    (async () => {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json, text/plain, */*',
                                'Content-Type': 'application/json',
                                'X-Http-Method-Override': 'delete'
                            },
                            body: JSON.stringify({
                                _action: 'delete',
                            })
                        });

                        if (response.status === 204) {
                            that.notifications.splice(that.notifications.findIndex(notification => notification.id == id), 1);
                        }
                    })();
                },

                edit() {

                    let url = this.url + `&id=${this.data.id}`,
                        that = this;

                    (async () => {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json, text/plain, */*',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(Object.assign(this.data, {_action: 'edit'}))
                        });

                        if (response.status === 205)
                        {
                            let idx = that.notifications.findIndex(notification => notification.id == this.data.id);
                            that.notifications[idx] = this.data;
                            that.reset();
                        }
                    })();

                },

                get component() {
                    return this;
                }
            };
        }

        if (data)
        {
            this.instance.initialize(data);

            if (data.debug)
            {
                if (!window.kAlpine) {
                    window.kAlpine = {};
                }

                window.kAlpine.notifiable = this.instance;
            }
        }

        return this.instance;
    }
};

