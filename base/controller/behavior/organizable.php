<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ControllerBehaviorOrganizable extends Library\ControllerBehaviorAbstract
{
    /**
     * Populated in before.edit to see what changed in after.edit
     *
     * @var array
     */
    protected $_category_cache = [];

    /**
     * Populated in before.edit to hold a list of files being replaced
     *
     * @var array
     */
    protected $_path_cache = [];

    /**
     * Populated in before.delete to see what needs to go in after.edit
     *
     * @var array
     */
    protected $_delete_cache = [];

    public function getFolderMismatches($callback = null)
    {
        $query = $this->getObject('database.query.select')
                      ->columns([
                          'id'  => 'tbl.easydoc_document_id',
                          'name' => "SUBSTRING_INDEX(tbl.storage_path, '/', -1)",
                          'folder' => "LEFT(tbl.storage_path, LENGTH(tbl.storage_path)-LENGTH(SUBSTRING_INDEX(tbl.storage_path, '/', -1))-1)",
                          'destination' => 'cf.folder'
                      ])
                      ->table(['tbl' => 'easydoc_documents'])
                      ->join(['cf' => 'easydoc_category_folders'], 'cf.easydoc_category_id = tbl.easydoc_category_id')
                      ->where('cf.easydoc_category_id IS NOT NULL')
                      ->where('tbl.storage_type = :storage_type')
                      ->where('LEFT(tbl.storage_path, LENGTH(cf.folder)) <> cf.folder')
                      ->bind(['storage_type' => 'file']);

        if (is_callable($callback)) {
            call_user_func($callback, $query);
        }

        return $this->getObject('database.driver.mysqli')->select($query, Library\Database::FETCH_OBJECT_LIST);
    }

    /**
     * Caches current values for categories
     *
     * @param Library\ControllerContextInterface $context
     */
    protected function _beforeEdit(Library\ControllerContextInterface $context)
    {
        if ($this->getMixer()->getIdentifier()->getName() !== 'document')
        {
            $entities = $this->getModel()->fetch();

            foreach ($entities as $entity)
            {
                $this->_category_cache[$entity->id] = (object) [
                    'parent_id'        => $entity->getParentId(),
                    'slug'             => $entity->slug,
                    'folder'           => $entity->folder,
                    'automatic_folder' => 1,
                ];
            }
        }
        else {
            $entities = $this->getModel()->fetch();
            $data     = $context->request->data;

            foreach ($entities as $entity)
            {
                if ($entity->storage_type === 'file' && $data->storage_path && ($entity->storage_path !== $data->storage_path)) {
                    $this->_path_cache[$entity->id] = $entity->storage_path;
                }
            }
        }
    }

    /**
     * Caches the folders to be deleted
     *
     * @param Library\ControllerContextInterface $context
     */
    protected function _beforeDelete(Library\ControllerContextInterface $context)
    {
        if ($this->getMixer()->getIdentifier()->getName() !== 'document')
        {
            $ids = [];
            $entities = $this->getModel()->fetch();

            foreach ($entities as $entity) {
                $ids[] = $entity->id;
                foreach ($entity->getDescendants() as $descendant) {
                    $ids[] = $descendant->id;
                }
            }

            $this->_delete_cache = array_unique($ids);
        }

    }

    /**
     * Deletes relations for deleted categories
     *
     * @param Library\ControllerContextInterface $context
     */
    protected function _afterDelete(Library\ControllerContextInterface $context)
    {
        if ($this->getMixer()->getIdentifier()->getName() === 'document')
        {
            foreach ($context->result as $entity)
            {
                if ($entity->getStatus() === Library\Database::STATUS_DELETED && $entity->storage_type === 'file')
                {
                    $state = [
                        'storage_type' => 'file',
                        'storage_path' => $entity->storage_path
                    ];

                    if (!$this->getObject('com:easydoc.model.documents')->setState($state)->count())
                    {
                        $file = $entity->storage;

                        try {
                            $this->_getFileController()
                                 ->container('easydoc-files')->folder($file->folder)->name($file->name)
                                 ->delete();
                        }
                        catch (\Exception $e) {
                            if (\Foliokit::isDebug()) {
                                $this->getObject('response')->addMessage($e->getMessage(), 'error');
                            }
                        }
                    }
                }
            }
        }

        if (count($this->_delete_cache))
        {
            $this->_deleteEmptyFolders($this->_delete_cache);

            $table = $this->getObject('com://admin/easydoc.database.table.category_folders');
            $query = $this->getObject('lib:database.query.delete')
                          ->table($table->getName())
                          ->where('easydoc_category_id IN :id')
                          ->bind(['id' => $this->_delete_cache]);

            $table->getDriver()->delete($query);
        }
    }

    protected function _deleteEmptyFolders($category_ids)
    {
        /*
         * Get a list of folders to be deleted
         */
        $table = $this->getObject('com://admin/easydoc.database.table.category_folders');
        $query = $this->getObject('lib:database.query.select')
                      ->columns(['folder'])
                      ->table($table->getName())
                      ->where('easydoc_category_id IN :id')
                      ->bind(['id' => $category_ids]);

        $list = $table->getDriver()->select($query, Library\Database::FETCH_FIELD_LIST);

        /*
         * Get a count of files&folders per entry
         */
        $query = $this->getObject('lib:database.query.select')
                               ->columns(['folder', 'count' => 'COUNT(*)'])
                               ->table(['nodes' => ModelNodes::getUnionQuery()])
                               ->where('folder IN :folder')
                               ->group('folder')
                               ->bind(['folder' => $list]);

        $results = $this->getObject('com:easydoc.database.table.nodes')->select($query, Library\Database::FETCH_OBJECT_LIST);

        /*
         * Flip the list and set counts per folder
         */
        $list = array_flip($list);
        foreach ($list as $path => $count) {
            $list[$path] = 0;
        }

        foreach ($results as $result) {
            if (isset($list[$result->folder])) {
                $list[$result->folder] = (int) $result->count;
            }
        }

        /**
         * If a child category is to be deleted, decrement the count of the parent so that it can be deleted as well
         */
        foreach ($list as $l => $count)
        {
            if ($count === 0)
            {
                $path = explode('/', $l);
                array_pop($path);

                for ($i = 1; $i <= count($path); $i++) {
                    $p = implode('/', array_slice($path, 0, $i));

                    if (isset($list[$p]) && $list[$p] > 0) {
                        $list[$p]--;
                    }
                }
            }
        }

        /*
         * Finally, let's delete those folders with no entries
         */
        foreach ($list as $path => $count)
        {
            if ($count === 0)
            {
                list($folder, $name) = $this->_splitPath($path);

                try {
                    $this->_getFolderController()
                         ->container('easydoc-files')->folder($folder)->name($name)
                         ->delete();
                } catch (\UnexpectedValueException $e) {
                    // invalid folder, probably because it's already deleted
                } catch (\Exception $e) {
                    if (\Foliokit::isDebug()) {
                        $this->getObject('response')->addMessage($e->getMessage(), 'error');
                    }
                }
            }
        }
    }

    /**
     * Moves files to the correct folder for documents
     * Saves automatic folder information for categories
     *
     * @param Library\ControllerContextInterface $context
     */
    protected function _afterAdd(Library\ControllerContextInterface $context)
    {
        $entity = $context->result->getIterator()->current();

        if ($entity->getIdentifier()->getName() === 'document') {
            $this->_moveFile($entity);
        }
        else {
            $this->_saveFolder($entity);
        }
    }

    /**
     * Moves files to the correct folder for documents
     * Saves automatic folder information for categories
     *
     * @param Library\ControllerContextInterface $context
     */
    protected function _afterEdit(Library\ControllerContextInterface $context)
    {
        foreach ($context->result as $entity)
        {
            if ($entity->getIdentifier()->getName() === 'document')
            {
                $this->_moveFile($entity);
                $this->_deleteOldFile($entity);
            }
            else
            {
                $this->_saveFolder($entity);

                if (isset($this->_category_cache[$entity->id]))
                {
                    $cache = $this->_category_cache[$entity->id];

                    if ($entity->automatic_folder && $cache->automatic_folder)
                    {
                        $new_name   = null;
                        $new_folder = null;
                        $parent     = $entity->getParent();

                        if ($entity->slug !== $cache->slug) {
                            $folder   = $parent->id ? $this->_getRelation($parent->id)->folder : '';
                            $new_name = $this->_getUniqueFolderName($entity, $folder);
                        }

                        if ($parent->id != $cache->parent_id) {
                            $new_folder = $parent->id ? $this->_getRelation($parent->id)->folder : '';
                        }

                        // $entity->folder check ensure we don't try moving empty folder to empty folder
                        if ($entity->folder && ($new_name !== null || $new_folder !== null))
                        {
                            list($folder, $name) = $this->_splitPath($entity->folder);

                            $controller = $this->_getFolderController();

                            $move = array();

                            if (isset($new_name) && $new_name != $name) {
                                $move['destination_name'] = $new_name;
                            }

                            if (isset($new_folder) && $new_folder != $folder) {
                                // This check ensure we don't try to move a folder into one of its subfolders
                                if (!$entity->folder || stripos($new_folder.'/', $entity->folder.'/') !== 0) {
                                    $move['destination_folder'] = $new_folder;
                                }

                            }

                            if (!empty($move)) {
                                try {
                                    $controller
                                        ->container('easydoc-files')->folder($folder)->name($name)
                                        ->move($move);
                                }
                                catch (Exception $e) {
                                    if (JDEBUG) {
                                        $this->getObject('response')->addMessage($e->getMessage(), 'error');
                                    }
                                }
                            }
                        }
                    }
                    /*
                     * scheduler will move the files in time for all other cases
                    elseif ($entity->automatic_folder && !$cache->automatic_folder) {}
                    elseif (!$entity->automatic_folder && $cache->automatic_folder) {}
                    elseif ($entity->folder !== $cache->folder) {}
                     */
                }
            }
        }
    }

    /**
     * Saves the category folder data
     * @param $category
     */
    protected function _saveFolder($category)
    {
        $relation = $this->_getRelation($category->id);
        $folder   = $relation->folder;

        if ($category->automatic_folder && ($relation->isNew() || !$relation->automatic || !$relation->folder)) {
            $folder = $category->folder ?: $this->_createFolder($category);
        } elseif ($category->folder !== null) {
            $folder = $category->folder;
        }

        $relation->setProperties(array(
            'id'        => $category->id,
            'automatic' => $category->automatic_folder,
            'folder'    => $folder
        ));

        $relation->save();
    }

    /**
     * Creates a folder for the given category. Makes sure the name is unique
     *
     * @param $category
     * @return bool|Library\ModelEntityInterface
     * @throws \Exception
     */
    protected function _createFolder($category)
    {
        $parent = $this->_getRelation($category->getParent()->id);
        $folder = $parent->isNew() ? '' : $parent->folder;
        $name   = $this->_getUniqueFolderName($category, $folder);

        $controller = $this->_getFolderController();

        try {
            $entity = $controller->getModel()->container('easydoc-files')->folder($folder)->name($name)->fetch();
        } catch (\UnexpectedValueException $e) {
            $entity = [];
        }

        if (!count($entity))
        {
            try {
                $entity = $controller->add([
                    'container' => 'easydoc-files',
                    'folder'    => $folder,
                    'name'      => $name
                ]);
            }
            catch (\Exception $e) {
                if (\Foliokit::isDebug()) {
                    throw $e;
                }
            }
        }

        return $entity->path;
    }

    protected function _splitPath($path)
    {
        $folder = pathinfo($path, PATHINFO_DIRNAME);
        $name   = \Foliokit\basename($path);

        if ($folder === '.') {
            $folder = '';
        }

        return [$folder, $name];
    }

    /**
     * Moves the file to the correct folder
     *
     * Movable behavior handles updating the paths in the document rows
     * Only moves files from the root folder, user probably chose the file deliberately from another folder
     *
     * @param $document
     */
    protected function _moveFile($document)
    {
        if ($document->storage_type === 'file')
        {
            $file     = $document->storage;
            $relation = $this->_getRelation($document->easydoc_category_id);

            if ($file->folder === '.' || $file->folder === '' || $file->folder === 'tmp' || $relation->folder !== $file->folder)
            {
                $destination_folder = null;

                // Move files out of root folder OR move from tmp to root if no category folder is defined
                if (!$relation->isNew() && !empty($relation->folder) && $relation->folder !== $file->folder) {
                    $destination_folder = $relation->folder;

                    if (!empty($destination_folder)) {
                        $basepath = $this->getObject('com:files.model.containers')->slug('easydoc-files')->fetch()->fullpath;
                        
                        if (!is_dir($basepath.'/'.$destination_folder)) {
                            $destination_folder = '';
                        }
                    }
                }
                elseif ($file->folder === 'tmp') {
                    $destination_folder = '';
                }

                if ($destination_folder !== null)
                {
                    $controller = $this->_getFileController();

                    $name      = $file->name;
                    $i         = 1;

                    while (true)
                    {
                        $entity = $controller->getModel()->container('easydoc-files')->folder($destination_folder)->name($name)->fetch();

                        if (count($entity)) {
                            $name = substr_replace($file->name, ' ('.$i.').'.$file->extension, -1*(strlen($file->extension)+1));
                            $i++;

                            continue;
                        }

                        break;
                    }

                    $destination = [
                        'destination_folder' => $destination_folder
                    ];

                    if ($name !== $file->name) {
                        $destination['destination_name'] = $name;
                    }

                    try {
                        $entity = $controller
                            ->container('easydoc-files')->folder($file->folder)->name($file->name)
                            ->move($destination);

                        // Making sure that the storage_path is updated. Movable is expected to do this but
                        // we've had clients for which is storage_path isn't updated after adding a new document
                        // Not using $document->save() approach to avoid failed status on model entity if the
                        // path was already updated (no affected rows)
                        $query = $this->getObject('lib:database.query.update')->table('easydoc_documents')
                                      ->where('easydoc_document_id = :id')->values('storage_path = :storage_path')
                                      ->bind(['id' => $document->id, 'storage_path' => $entity->path]);

                        $document->getTable()->getDriver()->update($query);

                        // Make sure that entity is in sync with the DB table
                        $document->setProperty('storage_path', $entity->path, false);
                    }
                    catch (\Exception $e) {
                        if (\Foliokit::isDebug()) {
                            $this->getObject('response')->addMessage($e->getMessage(), 'error');
                        }
                    }
                }
            }
        }
    }

    /**
     * Generates a unique folder name for the give ncategory
     * @param $category
     * @param $folder
     * @return string
     */
    protected function _getUniqueFolderName($category, $folder)
    {
        $name = $this->_getFolderName($category);
        $original_name = $name;
        $table = $this->getObject('com:easydoc.database.table.category_folders');
        $counter = 1;
        while (true) {
            $query = $this->getObject('database.query.select')->table($table->getName())
                          ->where('easydoc_category_id <> :id')
                          ->where('folder = :folder')
                          ->bind([
                              'id' => $category->id,
                              'folder' => ($folder ? $folder.'/' : '').$name
                          ]);

            if (!$table->count($query)) {
                break;
            }

            $name = $original_name.'-'.$counter;
            $counter++;
        }

        return strtolower($name);
    }

    /**
     * Creates the folder name based on slug, if all else fails returns the entity ID
     *
     * @param $entity
     * @return string
     */
    protected function _getFolderName($entity)
    {
        $name = trim($entity->slug);

        // Try to preserve UTF characters, fallback is needed since PCRE might be missing unicode support
        try {
            $name = preg_replace('#[^a-zA-Z0-9_\.\-~\p{L}\p{N}\s ]#u', '', $name);

            if (is_null($name) || $name === false) {
                throw new \RuntimeException('No result');
            }
        } catch (\Exception $e) {
            // try ascii
            $name = preg_replace('#[^a-zA-Z0-9_\.\-~\s ]#', '', $name);
        }

        $search = [
            '#(\.){2,}#', // remove multiple . characters
            '#^\.#', // strip leading period
            '#\.$#', // strip trailing period
            '#[\?:\#\*"@+=;!><&\.%()\]\/\'\\\\|\[]#', // forbidden characters
            '/\xE3\x80\x80/', // Replace double byte whitespaces by single byte (East Asian languages)
        ];

        $name = preg_replace($search, '', $name);

        if (empty($name)) {
            $name = $entity->id;
        }

        return strtolower($name);
    }

    /**
     * @param int $category_id
     * @return mixed
     */
    protected function _getRelation($category_id)
    {
        $relation = $this->getObject('com:easydoc.database.table.category_folders')
                         ->select(['id' => $category_id], Library\Database::FETCH_ROW);

        return $relation;
    }

    /**
     * @param $entity
     * @return array
     */
    protected function _deleteOldFile($entity)
    {
        if (isset($this->_path_cache[$entity->id]))
        {
            $path = $this->_path_cache[$entity->id];

            if ($path !== $entity->storage_path)
            {
                $state = [
                    'storage_type' => 'file',
                    'storage_path' => $path
                ];

                if (!$this->getObject('com:easydoc.model.documents')->setState($state)->count())
                {
                    list($folder, $name) = $this->_splitPath($path);

                    $controller = $this->_getFileController();

                    try {
                        $controller
                            ->container('easydoc-files')->folder($folder)->name($name)
                            ->delete();
                    } catch (\Exception $e) {
                        if (\Foliokit::isDebug()) {
                            $this->getObject('response')->addMessage($e->getMessage(), 'error');
                        }
                    }
                }
            }
        }
    }

    /**
     * @return Library\ObjectInterface
     */
    protected function _getFolderController()
    {
        $controller = $this->getObject('com:files.controller.folder', [
            'behaviors' => [
                'com:easydoc.controller.behavior.movable',
                'com:easydoc.controller.behavior.syncable',
                'permissible' => [
                    'permission' => 'com:easydoc.controller.permission.yesman'
                ]
            ]
        ]);

        return $controller;
    }

    protected function _getFileController()
    {
        $controller = $this->getObject('com:files.controller.file', [
            'behaviors' => [
                'com:easydoc.controller.behavior.movable',
                'com:easydoc.controller.behavior.syncable',
                'permissible' => [
                    'permission' => 'com:easydoc.controller.permission.yesman'
                ]
            ]
        ]);

        return $controller;
    }
}