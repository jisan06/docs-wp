/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

"use strict";

kQuery(function($) {

    //delete items button
    var delete_items_btn = '#command-delete';

    //dowload items button
    var download_items_btn = '#command-download';

    //use the toolbar delete button data for all delete buttons
    var request_params = $(delete_items_btn).data('params');
    var request_prompt = $(delete_items_btn).data('prompt');

    //delete item button
    var delete_item_btn = 'a[data-action="delete-item"]';

    //checkboxes
    var item_checkbox = 'input[name="item-select"]';

    var toggable, deletable, deletable_container;

    var getTreeDeletableContainers = function(elem)
    {
        var containers = [];

        if (elem.data('document'))
        {
            var uuid = elem.data('document');

            if ($('tr.footable-row-detail [data-document="' + uuid + '"]').length) {
                containers.push($('tr.footable-row-detail [data-document="' + uuid + '"]').closest('tr'));
            }

            if ($('tr.easydoc_item').find('[data-document="' + uuid + '"]').length) {
                containers.push($('tr.easydoc_item').filter('[data-document="' + uuid + '"]'));
            }
        }

        return containers;
    };

    var getTableDeletableContainers = function(elem)
    {
        var containers = [];

        if (elem.data('document'))
        {
            var uuid = elem.data('document');

            if ($('tr.easydoc_item[data-document="' + uuid + '"]').next().hasClass('footable-row-detail')) {
                containers.push($('tr.easydoc_item[data-document="' + uuid + '"]').next());
            }

            if ($('tr.easydoc_item').find('[data-document="' + uuid + '"]').length) {
                containers.push($('tr.easydoc_item').filter('[data-document="' + uuid + '"]'));
            }
        }

        return containers;
    }

    //gallery view
    if($('.k-ui-namespace.com_easydoc .koowa_media--gallery').length) {
        deletable = delete_item_btn;
        toggable = '.koowa_media__item__content';
        deletable_container = '.koowa_media__item';
        // tree table layout
    } else if($('.k-ui-namespace.com_easydoc .easydoc_list_layout--tree .easydoc_table_layout').length) {
        deletable = delete_item_btn;
        toggable = null;
        deletable_container = getTreeDeletableContainers;
        // table layout
    } else if ($('.k-ui-namespace.com_easydoc .easydoc_table_layout').length) {
        deletable = delete_item_btn;
        toggable = null;
        deletable_container = getTableDeletableContainers;
    } else {
        deletable = delete_item_btn;
        toggable = null;
        deletable_container = null;
    }

    $(delete_items_btn).addClass('k-is-disabled disabled').data('prompt', false);
    $(download_items_btn).addClass('k-is-disabled disabled');

    var deleteItem = function(element) {

        var elem   = $(element),
            path   = elem.data('url')    || elem.find(item_checkbox).data('url') || elem.next().find(item_checkbox).data('url'),
            data   = elem.data('params') || request_params;

        if (path) {
            if (elem.data('ajax') === false) {
                new Koowa.Form({
                    'method': 'post',
                    'url'   : path,
                    'params': data
                }).submit();
            } else {
                $.ajax({
                    method : 'post',
                    url : path,
                    data : data,
                    beforeSend : function () {
                        elem.addClass('k-is-disabled disabled');
                    }
                }).done(function()
                {
                    var containers = [];

                    if(deletable_container)
                    {
                        if (typeof deletable_container !== 'function')
                        {
                            var selectors = deletable_container;

                            if (!Array.isArray(selectors)) {
                                selectors = [selectors];
                            }

                            $.each(selectors, function(idx, selector) {
                                containers.push(elem.closest(selector));
                            });
                        }
                        else containers = deletable_container(elem);
                    }
                    else containers.push(elem);

                    $.each(containers, function (idx, container) {
                        container.fadeOut(300, function () {
                            container.remove();
                        });
                    });

                    const event = new Event('easydoc_item_deleted');

                    document.body.dispatchEvent(event); // Notify that the item got deleted
                });
            }
        }
    };

    var canExecute = function(action)
    {
        var checkboxes = $(item_checkbox + ':checked'), result = checkboxes.length ? true : false;

        $(item_checkbox + ':checked').each(function(idx, checkbox)
        {
            if (!$(checkbox).data('can-' + action)) {
                result = false;
            }

            if (!result) return false;
        });

        return result;
    };

    //checkbox event handler
    $('body').on('click', item_checkbox, function( event )
    {
        var $this = $(this);

        if ((!$this.data('canDownload') || $this.data('storageType') !== 'file') && !$this.data('canDelete'))
        {
            // User cannot execute batch actions on this item

            $this.prop('disabled', true);

            $this.parent().ktooltip({
                html: true,
                title: Foliokit.translate('You are not authorized to execute batch actions on this item'),
                placement: 'right',
                delay: {show: 200, hide: 50},
                container: '.k-ui-namespace'
            });
        }

        if (toggable) {
            $(this).closest(toggable).toggleClass('selected');
        }

        if(canExecute('delete')) {
            $(delete_items_btn).removeClass('k-is-disabled disabled');
        } else {
            $(delete_items_btn).addClass('k-is-disabled disabled');
        }

        /*if(canExecute('download')) {
            $(download_items_btn).removeClass('k-is-disabled disabled');
        } else {
            $(download_items_btn).addClass('k-is-disabled disabled');
        }*/
    }).on('click', delete_item_btn, function(event)
    {
        //delete item event handler

        event.preventDefault();

        var $this = $(this),
            elem = $this.closest(deletable),
            prompt = $this.data('prompt') || request_prompt;

        if ($this.hasClass('k-is-disabled') || $this.hasClass('disabled')) {
            return;
        }

        if (confirm(prompt)) {
            deleteItem(elem);
        }
    }).on('click', delete_items_btn, function (event)
    {
        event.preventDefault();

        var $this = $(this);

        if ($this.hasClass('k-is-disabled') || $this.hasClass('disabled')) {
            return;
        }

        var items = $(item_checkbox + ':checked');

        if(items.length && confirm(request_prompt))
        {
            $.each(items, function(index, checkbox)
            {
                var elem;

                if (deletable_container)
                {
                    if (typeof deletable_container === 'function')
                    {
                        elem = $(checkbox).closest('tr');

                        if (!elem.hasClass('easydoc_item')) {
                            elem = elem.prev();
                        }
                    }
                    else elem = $(checkbox).parents(deletable_container).find(deletable);
                }
                else elem = $(checkbox).closest(deletable);

                deleteItem(elem);
            });
        }

        $(delete_items_btn).addClass('k-is-disabled disabled');
        $(download_items_btn).addClass('k-is-disabled disabled');
    });
});
