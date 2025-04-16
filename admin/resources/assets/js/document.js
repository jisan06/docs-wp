/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

if (typeof EasyDoc=== 'undefined') {
    EasyDoc= {};
}

(function ($) {

    var humanizeFileName = function (name) {
        // strip extension
        name = name.substr(0, name.lastIndexOf('.'));

        // Replace - _ . with space character
        name = name.replace(/[\-_.]/g, ' ');

        // Trim the whitespaces
        name = $.trim(name.replace(/[\s]{2,}/g, ' '));

        // First character uppercase
        name = name.charAt(0).toUpperCase() + name.substr(1);

        return name;
    };

    $(function () {
        var form = $('.k-js-form-controller'),
            controller = form.data('controller');

        var int = setInterval(function () {
            if (controller.store) {
                clearInterval(int);

                Koowa.EntityStore.createFormBinding(controller.store, 'title', form);

                var entity = controller.store.state.entity,
                    setFileAndFolder = function (path) {
                        var properties = {
                            folder: path.substr(0, path.lastIndexOf('/')),
                            file: path.substr(path.lastIndexOf('/') + 1),
                            extension: path.substr(path.lastIndexOf('.') + 1).toLowerCase()
                        };

                        if (!entity.title) {
                            properties.title = entity.automatic_humanized_titles == 1 ? humanizeFileName(properties.file) : properties.file;
                        }

                        controller.store.commit('setProperty', properties);
                    },
                    setIcon = function (extension) {
                        var element = $('#params_icon');

                        if (extension && element.val() === 'default') {
                            if (element.val().indexOf('icon:') !== 0) {
                                /** @namespace EasyDoc.icon_map */
                                $.each(EasyDoc.icon_map, function (key, value) {
                                    if ($.inArray(extension, value) !== -1) {
                                        element.val(key).trigger('change');
                                    }
                                });
                            }
                        }
                    };

                setFileAndFolder(entity.storage_path);
                setIcon(entity.extension);

                controller.store.watch(function (state) {
                    return state.entity.storage_path;
                }, setFileAndFolder);

                controller.store.watch(function (state) {
                    return state.entity.extension;
                }, setIcon);
            }
        }, 100);


        // Make hits editable hits-container
        var hits_container = $('#hits-container');
        hits_container.on('click', 'a', function (e) {
            e.preventDefault();
            hits_container.find('span').text('0');

            $('<input type="hidden" class="required" size="25" name="hits" maxlength="11" />')
                .val(0)
                .appendTo(hits_container);
            $(this).remove();
        });
    });


})(kQuery);

EasyDoc.Textcounter = function () {
    return {
        text: '',
        remaining: 0,
        limit: null,

        setRemaining: function () {
            var length = this.text.length;

            // Count the line breaks
            var lineBreaks = this.text.match(/(\r\n|\n|\r)/g);
            if (lineBreaks !== null) {
                length += lineBreaks.length;
            }

            this.remaining = this.limit - length;
        },
        init: function (defaults = {}) {
            if (this.limit === null) {
                this.limit = defaults.limit || this.$refs.textarea.getAttribute('maxlength');
            }

            this.setRemaining();

            this.$watch('text', function () {
                this.setRemaining();
            }.bind(this));
        }
    }
};

EasyDoc.StatusSwitcher = function () {
    return {
        status: null,
        publishOn: null,
        unpublishOn: null,
        init: function (defaults = {}) {
            var that = this;

            this.publishOnElement = kQuery(this.$refs.publishOn);
            this.unpublishOnElement = kQuery(this.$refs.unpublishOn);

            this.publishOnElement.on('change', function () {
                that.publishOn = that.publishOnElement.val();
            });
            this.unpublishOnElement.on('change', function () {
                that.unpublishOn = that.unpublishOnElement.val();
            });

            this.publishOn = this.publishOnElement.val();
            this.unpublishOn = this.unpublishOnElement.val();

            if (defaults.status) {
                this.status = defaults.status;
            }

            if (this.status === '1' && (this.unpublishOn || this.publishOn)) {
                this.status = '2';
            }
        }
    }
}