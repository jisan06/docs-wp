/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

var EasyDoc= EasyDoc|| {};

(function($) {
EasyDoc.Dialog = Koowa.Class.extend({
    initialize: function(options) {
        this.supr();

        options.view = $(options.view);
        options.button = $(options.button, options.view);
        options.open_button = $(options.open_button);

        this.setOptions(options);
        this.attachEvents();
    },
    attachEvents: function() {
        var self = this;

        if (this.options.open_button) {
            this.options.open_button.click(function(event) {
                event.preventDefault();

                self.show();
            });
        }

        if (this.options.view.find('form')) {
            this.options.view.find('form').submit(function(event) {
                event.preventDefault();

                self.submit();
            });
        }
    },
    show: function() {
        var options = this.options,
            count = Koowa.Grid.getAllSelected().length;

        if (options.open_button.hasClass('k-is-unauthorized') || !count) {
            return;
        }

        $.magnificPopup.open({
            items: {
                src: $(options.view),
                type: 'inline'
            }
        });
    },
    hide: function() {
        $.magnificPopup.close();
    },
    submit: function() {
        var controller = $('.k-js-grid-controller').data('controller'),
            context = {},
            data = this.getData();

        if (data && Koowa.Grid.getAllSelected().length) {
            context.validate = true;
            context.data     = data;
            context.data[controller.token_name] = controller.token_value;
            context.action = 'edit';

            controller.trigger('execute', [context]);
        }
    },
    getData: function() {
        return null;
    }
});

EasyDoc.DuplicateDialog = EasyDoc.Dialog.extend({
    initialize: function(options) {
        options = {
            view: $(options.view),
            button: $(options.button, options.view),
            open_button: $(options.open_button),
            tree: $(options.view).find('.k-js-tree-container'),
            category_selector: $(options.category_selector)
        };

        this.supr(options);
    },
    submit: function() {
        var controller = $('.k-js-grid-controller').data('controller'),
            context = {},
            data = this.getData();

        if (data && Koowa.Grid.getAllSelected().length) {
            context.validate = true;
            context.data     = data;
            context.data[controller.token_name] = controller.token_value;
            context.action = 'copy';

            controller.trigger('execute', [context]);
        }
    },
    attachEvents: function() {
        this.supr();

        var self = this;

        if (this.options.category_selector) {
            this.options.category_selector.on('change', function(e) {
                self.options.button.prop('disabled', !$(this).val());
            });
        }
    },
    getData: function() {
        var selected = this.options.category_selector.val();

        if (selected) {
            return {
                easydoc_category_id: selected
            };
        } else {
            return null;
        }
    }
});

EasyDoc.MoveDialog = EasyDoc.Dialog.extend({
    initialize: function(options) {
        options = {
            view: $(options.view),
            button: $(options.button, options.view),
            open_button: $(options.open_button),
            tree: $(options.view).find('.k-js-tree-container'),
            category_selector: $(options.category_selector)
        };

        this.supr(options);
    },
    attachEvents: function() {
        this.supr();

        var self = this;

        if (this.options.category_selector) {
            this.options.category_selector.on('change', function(e) {
                self.options.button.prop('disabled', !$(this).val());
            });
        }
    },
    getData: function() {
        var selected = this.options.category_selector.val();

        if (selected) {
            return {
                easydoc_category_id: selected
            };
        } else {
            return null;
        }
    }
});


EasyDoc.BatchDialog = EasyDoc.Dialog.extend({
    initialize: function(options) {
        options = {
            view: $(options.view),
            button: $(options.button, options.view),
            open_button: $(options.open_button),
            tree: $(options.view).find('.tree-container'),
            category_selector: $(options.category_selector)
        };

        this.supr(options);
    },
    attachEvents: function() {
        this.supr();

        var self = this;

        if (this.options.category_selector) {
            this.options.category_selector.on('change', function(e) {
                self.options.button.prop('disabled', !$(this).val());
            });
        }
    },
    getData: function() {
        var form_data = $('.k-js-batch-form').serializeArray(),
            data = {};
            name_check = /\[\]/g;
            can_send = false;

        $.each(form_data, function(i, field) {
            var name = field.name;

            if (!field.value || field.value === '') {
                return;
            }

            can_send = true;

            if (name.search(name_check) === -1) {
                data[name] = field.value;
            } else {
                name = name.replace(name_check, '');

                if (!data[name]) {
                    data[name] = [];
                }

                data[name].push(field.value);
            }
        });

        if (can_send) {
            return data;
        }

        return null;
    }
});

$(function () {
    new EasyDoc.DuplicateDialog({
        view: '#document-duplicate-modal',
        button: '.k-button--primary',
        open_button: '#command-copy',
        category_selector: '#document_duplicate_target'
    });

    new EasyDoc.MoveDialog({
        view: '#document-move-modal',
        button: '.k-button--primary',
        open_button: '#command-move',
        category_selector: '#document_move_target'
    });

    new EasyDoc.BatchDialog({
        view: '#document-batch-modal',
        button: '.k-button--primary',
        open_button: '#command-batch'
    });
});


})(window.kQuery);
