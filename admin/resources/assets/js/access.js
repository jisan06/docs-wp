/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

(function($)
{
    $(document).ready(function()
    {
        var users = $('#input-option-list-users');
        var usergroups = $('#input-option-list-usergroups');

        users.click(function()
        {
            $('#easydoc-access-usergroups').hide();
            $('#easydoc-access-users').show();
        });

        usergroups.click(function()
        {
            $('#easydoc-access-usergroups').show();
            $('#easydoc-access-users').hide();
        });
    });
})(kQuery);