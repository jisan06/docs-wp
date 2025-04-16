/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

kQuery(function($){
  var grid = $('.k-js-grid-controller'),
      controller = grid.data('controller'),
      delete_button = $('#command-delete'),
      message = Koowa.translate('You cannot delete a category while it still has documents'),
      countDocuments = function()
	  {
		var count = 0;

		Koowa.Grid.getAllSelected().each(function() {
			count += parseInt($(this).data('documents-count'), 10);
		});

		return count;
      },
	  canDelete = function()
	  {
		let result = true;

		Koowa.Grid.getAllSelected().each(function()
		{
			let permissions = $(this).data('permissions');

			if (!permissions.delete) {
				result = false;
				return;
			}
		});

		return result;
	  };

  controller.toolbar.find('a.toolbar').ktooltip({
      placement: 'bottom'
  });

  grid.on('k:afterValidate', function() {
      if (countDocuments() && canDelete()) {
          delete_button.addClass('k-is-disabled');
          delete_button.ktooltip('destroy');
          delete_button.ktooltip({title: message, placement: 'bottom'});
      }
  });

});
