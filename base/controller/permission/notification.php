<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

/**
 * Connect controller permission
 *
 * @author Arunas Mazeika <https://github.com/amazeika>
 */
class ControllerPermissionNotification extends ControllerPermissionAbstract
{
    public function canAdd()
    {
        $result = false;

        $request = $this->getMixer()->getRequest();

        // User can add a notification to a category editable by the user

        if ($request->isPost() && !empty($row = $request->getData()->row)) {
            $result = $this->_canExecute('edit_category', $row);
        }

        return $result;
    }

    public function canDelete()
    {
        return $this->canEdit();
    }

    public function canEdit()
    {
        $result = false;

        $notification = $this->getMixer()->getModel()->fetch();

        // User can edit notification if it can edit the category where it was defined

        if ($notification->table == 'easydoc_categories') {
            $result = $this->getObject('com:easydoc.model.categories')->id($notification->row)->fetch()->canEdit();
        }

        return $result;
    }
}