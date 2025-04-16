/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

"use strict";

kQuery(function($) {
    var item_checkbox_selector = '.k-js-item-select';
    var item_row_selector = '.easydoc_item';
    var download_button_selector = '.k-js-multi-download';
    var download_button = $(download_button_selector);
    var download_spinner = $('<span class="k-loader" style="display: none">Loading…</span>');

    // if there are multiple buttons for the grid such as delete
    var has_delete_button = kQuery('.k-js-toolbar').find('[id="command-delete"]').length === 1;

    if (!has_delete_button)
    {
        $(item_checkbox_selector).each(function(i, checkbox)
        {
            var $checkbox = $(checkbox);

            if ($checkbox.data('storageType') !== 'file')
            {
                $checkbox.parent().ktooltip({
                    title: Foliokit.translate('Remote files cannot be downloaded in batch'),
                    placement: 'right',
                    delay: {show: 200, hide: 50},
                    container: '.k-ui-namespace'
                });

                $checkbox.prop('disabled', true);
            }
            else if (!$checkbox.data('canDownload'))
            {
                $checkbox.parent().ktooltip({
                    title: Foliokit.translate('You are not authorized to download the selected file'),
                    placement: 'right',
                    delay: {show: 200, hide: 50},
                    container: '.k-ui-namespace'
                });

                $checkbox.prop('disabled', true);
            }
        });
    }

    download_button.ktooltip({
        title: Foliokit.translate('Preparing download'),
        placement: 'bottom',
        delay: {show: 200, hide: 50},
        trigger: 'manual',
        container: '.k-ui-namespace'
    });

    var startSpinner = function () {
        download_button.ktooltip('show');
        download_spinner.css('display', '');
    };
    var stopSpinner = function () {
        download_button.ktooltip('hide');
        download_spinner.css('display', 'none');
    };

    var enableButton = function() {
        download_button.removeClass('k-is-disabled disabled');
    };

    var disableButton = function () {
        download_button.addClass('k-is-disabled disabled');
    };
    var setButtonStatus = function() {
        var checked = $(item_checkbox_selector + ':checked');
        if(checked.length) {
            var can_download = true;
            $.each(checked, function (index, checkbox) {
                var $checkbox = $(checkbox);

                if (!$checkbox.data('canDownload') || $checkbox.data('storageType') !== 'file')
                {
                    can_download = false;
                    return false;
                }
            });

            if (can_download) {
                enableButton();
            } else {
                disableButton();
            }

        } else {
            disableButton();
        }
    };

    var isButtonEnabled = function () {
        return !download_button.hasClass('k-is-disabled');
    };

    download_button.append(download_spinner);

    setButtonStatus();

    $('body').on('click', item_checkbox_selector, setButtonStatus)
        .on('click', item_row_selector, setButtonStatus);

    download_button.on('click', function (event) {
        event.preventDefault();

        if (!isButtonEnabled()) {
            return;
        }

        var items = $(item_checkbox_selector + ':checked');

        if (items.length) {

            var ids = [];

            $.each(items, function (index, checkbox) {
                var $checkbox = $(checkbox);

                if ($checkbox.data('canDownload')) {
                    ids.push($checkbox.data('id'));
                }
            });

            if (ids) {
                var url = download_button.data('url');
                $.ajax({
                    method: 'post',
                    url: url + (url.indexOf('?') === -1 ? '?' : '&') + $.param({id: ids}),
                    dataType: 'json',
                    data: {
                        _action: 'compress'
                    },
                    beforeSend: function () {

                        startSpinner();
                        disableButton();
                    }
                }).done(function(response) {
                    if (typeof response === 'object' && response.route)
                    {
                        window.location.href = response.route;

                        enableButton();
                        stopSpinner();
                    }
                }).fail(function () {
                    enableButton();
                    stopSpinner();
                });
            }

        }
    });
});