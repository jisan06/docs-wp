/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

kQuery(function($) {
    var grid   = $('.k-js-grid-controller'),
        controller = grid.data('controller'),
        buttons  = controller.buttons,
        no_action_buttons = $('#command-move, #command-batch, #command-copy, #command-assign, #command-remove, #command-select');

    if (no_action_buttons.length) {
        $.merge(buttons, no_action_buttons);
    }

    controller.toolbar.find('a.toolbar').ktooltip({
        placement: 'bottom'
    });

    grid.on('k:afterValidate', function() {
        var message  = 'You are not authorized to perform the %s action on these items',
            selected = Koowa.Grid.getAllSelected(),
            checkAction = function(action, selected)
            {
                var result = true;

                if (selected.length === 0) {
                    return false;
                }

                if (!action) {
                    return true;
                }

                selected.each(function()
                {
                    var permissions = $(this).data('permissions');

                    if (!permissions || result == false) {
                        return result;
                    }

                    if (typeof permissions[action] !== 'undefined') result = permissions[action]
                });

                return result;
            };

        buttons.each(function() {
            var button = $(this),
                action = button.data('action');

            if (!action && button.data('permission')) {
                action = button.data('permission');
            }

            /*if (button.hasClass('k-is-unauthorized')) {
                button.addClass('k-is-disabled');
                button.attr('data-original-title', message.replace('%s', action));
                return;
            }*/

            if (checkAction(action, selected) && selected.length > 0) {
                button.removeClass('k-is-disabled');
                button.attr('data-original-title', '');
            } else {
                button.addClass('k-is-disabled');
                if(selected.length > 0) {
                    button.attr('data-original-title', message.replace('%s', action));
                }
            }
        });

        return true;
    });

    grid.trigger('k:afterValidate');
});