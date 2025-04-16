<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Component\Files;
use EasyDocLabs\Library;

/**
 * Used by the node controller to change document paths after moving files
 */
class ControllerBehaviorMovable extends Library\ControllerBehaviorAbstract
{
    const TEMP_FOLDER = 'tmp';

    protected $_path_cache = [];

    protected function _afterBrowse(Library\ControllerContextInterface $context)
    {
        // TODO This is most likely not the right location for this handler. This triggers when the uploader checks for the existance of a file prior upload
        //      so that the user is prompted to override or not. Moreover this code was also failing because result is holding a single row which is not composable
        //      (problem reproduceable on headless app demo when upload files). The composable check has been temporarily added to prevent this error, but this
        //      needs further work and debugging.

        if ($context->result instanceof Library\ModelEntityComposable)
        {
            foreach ($context->result as $entity)
            {
                if (substr($entity->path, 0, 3) === static::TEMP_FOLDER) {
                        $context->result->remove($entity);
                }
            }
        }
    }

    /**
     * Automatically create tmp folder before uploading anything into it
     *
     * @param Library\ControllerContextInterface $context
     * @throws \Exception
     */
    protected function _beforeAdd(Library\ControllerContextInterface $context)
    {
        if ($this->getMixer()->getIdentifier()->name === 'file'
            && $context->request->data->container === 'easydoc-files'
            && $context->request->data->folder === static::TEMP_FOLDER)
        {
            $controller = $this->getObject('com:files.controller.folder')->container('easydoc-files');
            $folder     = $controller->getModel()->name(static::TEMP_FOLDER)->fetch();

            if ($folder->isNew())
            {
                try {
                    $controller->add([
                        'container' => 'easydoc-files',
                        'overwrite' => 1,
                        'name'      => static::TEMP_FOLDER
                    ]);
                }
                catch (\Exception $e) {
                    if (\Foliokit::isDebug()) throw $e;
                }

            }
        }
    }

    /**
     * Update document modified_on timestamps when the attached file is overwritten
     *
     * @param Library\ControllerContextInterface $context
     */
    protected function _afterAdd(Library\ControllerContextInterface $context)
    {
        if ($context->request->data->overwrite && $context->result instanceof Files\ModelEntityFile)
        {
            $file = $context->result;

            $documents = $this->getObject('com://admin/easydoc.model.documents')
                              ->storage_path($file->path)->storage_type('file')
                              ->limit(100)
                              ->fetch();

            if (count($documents))
            {
                foreach($documents as $document)
                {
                    // Force recalculation and hence a save since no other column has changed
                    $document->modified_by = -1;
                    $document->save();
                }
            }
        }
    }


    protected function _beforeMove(Library\ControllerContextInterface $context)
    {
        $entities = $this->getModel()->fetch();

        foreach ($entities as $entity)
        {
            $entity->setProperties($context->request->data->toArray());

            $from = $entity->path;
            $to   = $entity->destination_path;

            if (is_dir($entity->fullpath))
            {
                $from .= '/';
                $to   .= '/';
            }

            $this->_path_cache[$from] = $to;
        }
    }

    /**
     * Updates attached documents of the moved files
     *
     * Uses a database update query directly since moving folders might mean updating hundreds of rows.
     *
     * @param Library\ControllerContextInterface $context
     */
    protected function _afterMove(Library\ControllerContextInterface $context)
    {
        $table = $this->getObject('com:easydoc.model.documents')->getTable();

        $documents_query = $this->getObject('lib:database.query.update')
                                ->table($table->getName())
                                ->where("storage_type = :type")->bind(['type' => 'file']);

        $folders_query = $this->getObject('lib:database.query.update')
                              ->table('easydoc_category_folders');

        foreach ($this->_path_cache as $from => $to)
        {
            $from = Files\FilterPath::normalizePath($from);
            $to   = Files\FilterPath::normalizePath($to);

            $query = clone $documents_query;
            $query->bind(['from' => $from, 'to' => $to]);

            if (substr($from, -1) === '/') // Move folder
            {
                $query->values('storage_path = REPLACE(storage_path, :from, :to)')
                      ->where('storage_path LIKE :filter')->bind(['filter' => $from.'%']);
            }
            else // Move file
            {
                $query->values('storage_path = :to')
                      ->where('storage_path = :from');
            }

            $table->getDriver()->update($query);
        }

        // Move category folders
        foreach ($this->_path_cache as $from => $to)
        {
            if (substr($from, -1) === '/')
            {
                $from = substr($from, 0, -1);
                $to   = substr($to, 0, -1);

                $from = Files\FilterPath::normalizePath($from);
                $to   = Files\FilterPath::normalizePath($to);

                $query = clone $folders_query;
                $query->bind(['from' => $from, 'to' => $to]);

                $query->values("folder = CONCAT_WS('/', NULLIF(:to, ''), NULLIF(SUBSTRING(`folder`, LENGTH(:from)+2), ''))")
                      ->where('folder LIKE :filter')->bind(['filter' => $from.'%']);

                $table->getDriver()->update($query);
            }
        }
    }
}