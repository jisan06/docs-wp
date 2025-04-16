/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

kQuery(function($) {
    var grid   = $('.k-js-grid-controller'),
        controller = grid.data('controller');

    var selectedDocuments = $('#command-selected_documents');

    selectedDocuments.click(function(event) {
        event.preventDefault();

        if (selectedDocuments.hasClass('k-is-disabled')) {
            return;
        }

        var payload = {
            publisher: 'easy-docs-ait/attachments',
            event: 'selected-documents',
            selected: []
        };
        if (window !== window.parent) {
            var selected = [];
            Koowa.Grid.getAllSelected().each(function(a, b) {
                selected.push($(b).data('entity'))
            });

            payload.selected = selected;

            window.parent.postMessage(payload, '*');
        }
    });

});