/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

kQuery.validator.addMethod("storagepath", function(value) {
    return /^[0-9A-Za-z:_\-\/\\\.]+$/.test(value);
}, Koowa.translate('Folder names can only contain letters, numbers, dash, underscore or colons'));

kQuery(function($) {
    var extensions = $('#allowed_extensions'),
        tag_list = extensions.tagsInput({
            removeWithBackspace: false,
            width: '100%',
            height: '100%',
            animate: true,
            defaultText: Koowa.translate('Add another extension...')
        }),
        filetypes = extensions.data('filetypes'),
        labels = {
            'audio': Koowa.translate('Audio files'),
            'archive': Koowa.translate('Archive files'),
            'document': Koowa.translate('Documents'),
            'image': Koowa.translate('Images'),
            'video': Koowa.translate('Video files')
        },
        list = $('.k-js-extension-groups'),
        group = $('.k-js-extension-preset');

    $.each(filetypes, function(key, value) {
        var label = labels[key],
            el = $(group).clone();

        el.find('.k-js-extension-preset-label').text(label);
        el.find('button').data('extensions', value);
        list.append(el);
        el.show();
    });

    list.on('click', 'button', function(e) {
        e.preventDefault();

        var el = $(this),
            method = (el.hasClass('k-js-add') ? 'addTag' : 'removeTag'),
            extensions = el.data('extensions');

        $.each(extensions, function(i, extension) {
            tag_list[method](extension, {unique: true, mark_input: false});
        });
    });

    var evt = function()
    {
        let max_size = $('#maximum_size');

        if (max_size.length)
        {
            var value = max_size.val()*1048576;

            $('<input type="hidden" name="maximum_size" />').val(value).appendTo($('.k-js-form-controller'));
        }
    };

    $('.k-js-form-controller').on('k:beforeApply', evt).on('k:beforeSave', evt);

    $('.edit_document_path').click(function(event) {
        var $this = $(this);

        event.preventDefault();

        $this.parent().siblings('input').prop('disabled', false);
        $this.parents('.k-input-group').removeClass('k-input-group');
        $this.remove();
    });

    var checkbox      = $('.file_size_checkbox'),
        max_size      = $('#maximum_size'),
        last_value    = null,
        checkboxEvent = function() {
            var checked = checkbox.find('input').prop('checked');
            max_size.prop('disabled', checked);

            if (checked) {
                last_value = max_size.val();
                max_size.val('');
            } else if (last_value) {
                max_size.val(last_value);
            }
        };

    checkbox.change(checkboxEvent);
    checkboxEvent();
});

kQuery(function ($) {
    var buttons = {
        '.k-js-clear-cache': {
            tooltip: Foliokit.translate('Clearing cache…'),
            payload: {
                _action: 'clear_cache'
            },
            error: 'Could not clear cache',
        },
        '.k-js-refresh-license': {
            tooltip: Foliokit.translate('Activating…'),
            payload: {
                _action: 'refresh_license'
            },
            error: 'License in invalid',
            done: () => {
                window.location.reload(true);
            }
        }
    }

    for (let [button, data] of Object.entries(buttons)) {

        let actionButton = $(button);
        let spinner = $('<span class="k-loader" style="display: none">Loading…</span>');

        actionButton.append(spinner);

        actionButton.ktooltip({
            title: data.tooltip,
            placement: 'bottom',
            delay: {show: 200, hide: 50},
            trigger: 'manual',
            container: '.k-ui-namespace'
        });

        actionButton.click(function (event) {
            event.preventDefault();

            // Check license field empty or not
            if (data.payload._action === 'refresh_license') {
                let licenseField = $('#easydocs_license_key');
                if (!licenseField.length || licenseField.val().trim() === '') {
                    alert('Please enter a valid license key.');
                    return;
                }

                // If needed, you can also include license in payload like:
                data.payload.license = licenseField.val().trim();
            }
            $.ajax({
                method: 'post',
                url: $('.k-js-form-controller').attr('action'),
                dataType: 'json',
                data: data.payload,
                beforeSend: function () {
                    actionButton.addClass('k-is-disabled disabled').ktooltip('show');
                    spinner.css('display', '');
                }
            }).fail(function() {
                alert(data.error);
            }).done(function() {
                data.done ? data.done() : null;
            }).always(function () {
                actionButton.append('<span class="k-icon-check" style="color: green" aria-hidden="true"></span>');
                actionButton.removeClass('k-is-disabled disabled').ktooltip('hide');
                spinner.css('display', 'none');
                setTimeout(function () {
                    var check = actionButton.children('.k-icon-check');
                    check.hide(function() {
                        check.remove();
                    });
                }, 2000);
            });
        })
    }
});