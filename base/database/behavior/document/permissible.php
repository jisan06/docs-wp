<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class DatabaseBehaviorDocumentPermissible extends DatabaseBehaviorPermissible
{
    protected $_action_map = [
        'upload'   => 'upload_document',
        'download' => 'download_document',
        'edit'     => 'edit_document',
        'delete'   => 'delete_document'
    ];

    /**
     * Permission entity getter
     *
     * @param bool $default Tells if default permissions should be returned
     *
     * @return Library\ModelEntityInterface The permission entity
     */
    public function getPermission($default = false)
    {
        return $this->category->getPermission($default);
    }

    protected function _afterUpdate(Library\DatabaseContextInterface $context)
    {
        // Do nothing
    }

    protected function _afterDelete(Library\DatabaseContextInterface $context)
    {
        // Do nothing
    }
}
