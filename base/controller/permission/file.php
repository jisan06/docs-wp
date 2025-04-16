<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

/**
 * Document controller permissions
 */
class ControllerPermissionFile extends ControllerPermissionAbstract
{
    /**
     * Generic permission checker
     *
     * * Renders the view if the user can see any edit form in the component
     * * Renders the folder tree and allow upload if you can see any edit form in the component
     * * Renders nodes, files and thumbnails, container views only if the user can manage the component
     *
     * @return bool
     */
    public function canRender()
    {
        $result = false;

        if (!$this->getMixer()->isDispatched() || $this->canManage()) {
            $result = true;
        }

        if (!$result)
        {
            $view   = $this->getView()->getName();
            $format = $this->getRequest()->getFormat();

            if ($format === 'html' && $view === 'file') {
                $result = $this->_canExecute(['download_document']);
            }

            if (($format === 'html' && $view === 'files') || ($format === 'json' && in_array($view, ['nodes', 'file', 'files', 'folders', 'proxy']))) {
                $result = $this->_canExecute(['upload_document', 'add_category', 'edit_document']);
            }
        }

        return $result;
    }

    public function canRead()
    {
        return $this->canRender();
    }

    public function canBrowse()
    {
        return $this->canRender();
    }

    public function canAdd()
    {
        return $this->canRender();
    }

    public function canEdit()
    {
        return $this->canRender();
    }

    public function canDelete()
    {
        if (!$this->getRequest()->isGet())
        {
            $name = $this->getMixer()->getIdentifier()->name;

            if ($name === 'file' || $name === 'folder')
            {
                $request = $this->getRequest();
                $path    = ($request->query->folder ? $request->query->folder . '/' : '') . $request->query->name;

                $documents = $this->getObject('com:easydoc.model.documents')
                                  ->search_path($name === 'file' ? $path : $path.'/%')
                                  ->storage_type('file')
                                  ->fetch();

                $count = count($documents);

                if ($count)
                {
                    $translator = $this->getObject('translator');

                    if ($name === 'file')
                    {
                        $messages = [
                            $translator->translate('The document with the title {title} has this file attached to it. You should either change the attached file or delete the document before deleting this file.'),
                            $translator->translate('This file has {count} documents attached to it. You should either change the attached files or delete these documents before deleting this file.'),
                        ];
                    }
                    else
                    {
                        $messages = [
                            $translator->translate('The document with the title {title} has a file attached from this folder. You should either change the attached file or delete the document before deleting this folder.'),
                            $translator->translate('There are {count} documents that have a file attached from this folder. You should either change the attached files or delete these documents before deleting this folder.')
                        ];
                    }

                    $message = $translator->choose($messages, $count, [
                        'count' => $count,
                        'title' => $count == 1 ? $documents->top()->title : ''
                    ]);

                    throw new Library\ControllerExceptionActionFailed($message);
                }
            }
        }

        return $this->canManage();
    }

    public function canMove()
    {
        return $this->canDelete() && $this->canAdd();
    }

    public function canCopy()
    {
        return $this->canAdd();
    }
}