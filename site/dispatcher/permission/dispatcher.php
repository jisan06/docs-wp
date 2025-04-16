<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;

class DispatcherPermissionDispatcher extends Library\DispatcherPermissionAbstract
{
    protected static $_allowed_json_views = ['connect', 'document', 'download', 'category', 'flat', 'list', 'tree', 'submit', 'upload', 'tag', 'notification'];

    public function canDispatch()
    {
        if ($this->getMixer()->getRequest()->getFormat() === 'json') {
            $name = $this->getController()->getIdentifier()->getName();

            if ($name === 'user') {
                return $this->getObject('com://site/easydoc.controller.category')->canExecuteAny(['edit_document', 'edit_category', 'upload_document', 'add_category']);
            }

            if (!$this->getMixer()->getRequest()->getQuery()->has('routed') && !in_array($name, static::$_allowed_json_views)) {
                return false;
            }
        }

        return parent::canDispatch();
    }
}

