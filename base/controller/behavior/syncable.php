<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ControllerBehaviorSyncable extends Library\ControllerBehaviorAbstract
{
    protected $_path_cache = [];

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'priority'   => self::PRIORITY_LOW,
        ]);

        parent::_initialize($config);
    }

    protected function _afterAdd(Library\ControllerContextInterface $context)
    {
        $name = $this->getMixer()->getIdentifier()->getName();

        try
        {
            if ($name === 'file') {
                $this->addFile($context->result->path, $context->result);
            } else if ($name === 'folder') {
                $this->addFolder($context->result->path);
            }
        }
        catch (\Exception $e) {}
    }

    protected function _afterDelete(Library\ControllerContextInterface $context)
    {
        $name = $this->getMixer()->getIdentifier()->getName();

        if ($name === 'file')
        {
            foreach ($context->result as $entity)
            {
                $this->getObject('com:easydoc.model.files')
                     ->folder($entity->folder)->name($entity->name)
                     ->fetch()->delete();
            }
        }
        else if ($name === 'folder')
        {
            foreach ($context->result as $entity)
            {
                // Using model to fetch rows might return thousands of files in a rowset leading to memory errors
                if ($entity->path)
                {
                    $query = $this->getObject('database.query.delete')
                                  ->table('easydoc_files')
                                  ->where('(folder = :folder OR folder LIKE :folder_like)')
                                  ->bind([
                                      'folder' => $entity->path,
                                      'folder_like' => $entity->path.'/%'
                                  ]);

                    $this->getObject('com:easydoc.database.table.files')->getDriver()->delete($query);

                    $query = $this->getObject('database.query.delete')
                                  ->table('easydoc_folders')
                                  ->where('(folder = :folder OR folder LIKE :folder_like)')
                                  ->bind([
                                      'folder' => $entity->path,
                                      'folder_like' => $entity->path.'/%'
                                  ]);

                    $this->getObject('com:easydoc.database.table.folders')->getDriver()->delete($query);

                }

                $this->getObject('com:easydoc.model.folders')
                     ->folder($entity->folder)->name($entity->name)
                     ->fetch()->delete();
            }
        }
    }

    protected function _beforeMove(Library\ControllerContextInterface $context)
    {
        $entities = $this->getModel()->fetch();

        foreach ($entities as $entity) {
            $entity->setProperties($context->request->data->toArray());

            $this->_path_cache[] = [
                $entity->getIdentifier()->getName(), [$entity->folder, $entity->name], [$entity->destination_folder, $entity->destination_name]
            ];
        }
    }


    protected function _afterMove(Library\ControllerContextInterface $context)
    {
        foreach ($this->_path_cache as $row) {
            list($name, $from, $to) = $row;

            $result = $this->getObject(sprintf('com:easydoc.database.table.%ss', $name))->select([
                'folder' => (string)$from[0],
                'name'   => (string)$from[1]
            ]);

            if (isset($to[0])) {
                $result->folder = $to[0];
            }

            if (isset($to[1])) {
                $result->name = $to[1];
            }

            $result->save();

            // Update children folder and file paths
            if ($name === 'folder')
            {
                $from_path = ($from[0] ? $from[0].'/' : '') . $from[1];
                $to_path   = ($result->folder ? $result->folder.'/' : '') . $result->name;

                $query = $this->getObject('database.query.update')
                              ->values('folder = CONCAT_WS(\'/\', NULLIF(:to, \'\'), NULLIF(SUBSTRING(folder, LENGTH(:from)+2), \'\'))')
                              ->where('folder LIKE CONCAT(:from, \'%\')')
                              ->bind(['to' => $to_path, 'from' => $from_path]);

                $adapter = $this->getObject('com:easydoc.database.table.folders')->getDriver();

                $query->table('easydoc_folders');
                $adapter->update($query);

                $query->table('easydoc_files');
                $adapter->update($query);
            }
        }
    }

    protected function _beforeRender(Library\ControllerContextInterface $context)
    {
        if ($context->getRequest()->getQuery()->has('revalidate_cache')) {
            $this->syncFolders();
            $this->syncFiles();
        }
    }

    public function syncFiles()
    {
        $list     = $this->getFileList();
        $path     = $this->getObject('com:files.model.containers')->slug('easydoc-files')->fetch()->fullpath;
        $exclude  = ['.', '..', '.svn', '.htaccess', 'web.config', '.git', 'CVS', 'index.html', '.DS_Store', 'Thumbs.db', 'Desktop.ini'];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::CHILD_FIRST);
        $insert   = [];

        foreach ($iterator as $file)
        {
            if ($file->isDir() || in_array($file->getFilename(), $exclude) || substr($file->getFilename(), 0, 1) === '.') {
                continue;
            }

            $name = str_replace('\\', '/', $file->getPathname());
            $name = str_replace($path.'/', '', $name);

            if (preg_match('#(\/|^)\.[^\/\.]#i', $name)) {
                continue;
            }

            if (!isset($list[$name])) {
                $insert[] = $name;
            }
            else {
                // file is in the list, unset it, so the rest of list is deleted files
                unset($list[$name]);
            }
        }

        // Delete stale entries
        if (count($list))
        {
            $query = $this->getObject('database.query.delete')
                          ->table('easydoc_files')
                          ->where('easydoc_file_id IN :id')->bind(['id' => $list]);

            $this->getObject('com:easydoc.database.table.files')->getDriver()->delete($query);
        }

        // Add new files
        if (count($insert))
        {
            $query = $this->getObject('database.query.insert')
                          ->table('easydoc_files')
                          ->columns(['folder', 'name', 'modified_on']);

            $query_count = 0;

            for ($i = 0, $count = count($insert); $i < $count; $i++)
            {
                $file = $insert[$i];

                $query->values(array_merge($this->_splitPath($file), [$this->_getModifiedTime($path.'/'.$file)]));

                $query_count++;

                if ($query_count == 100 || $i == $count-1)
                {
                    $once = 1;
                    $string = str_replace('INSERT', 'INSERT IGNORE', $query->toString(), $once);

                    $this->getObject('lib:database.driver.mysqli')->execute($string);

                    $query->values = [];
                    $query_count = 0;
                }
            }
        }
    }

    public function syncFolders()
    {
        $list     = $this->getFolderList();
        $path     = $this->getObject('com:files.model.containers')->slug('easydoc-files')->fetch()->fullpath;
        $exclude  = ['.', '..', '.svn', '.htaccess', 'web.config', '.git', 'CVS', 'index.html', '.DS_Store', 'Thumbs.db', 'Desktop.ini'];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::CHILD_FIRST);
        $insert   = [];

        foreach ($iterator as $file)
        {
            if (!$file->isDir() || in_array($file->getFilename(), $exclude) || substr($file->getFilename(), 0, 1) === '.') {
                continue;
            }

            $name = str_replace('\\', '/', $file->getPathname());
            $name = str_replace($path.'/', '', $name);

            if (preg_match('#(\/|^)\.[^\/\.]#i', $name)) {
                continue;
            }

            if (!isset($list[$name])) {
                $insert[] = $name;
            }
            else {
                // file is in the list, unset it, so the remaining files in the list are deleted files
                unset($list[$name]);
            }
        }

        // Delete stale entries
        if (count($list))
        {
            $query = $this->getObject('database.query.delete')
                          ->table('easydoc_folders')
                          ->where('easydoc_folder_id IN :id')->bind(['id' => $list]);

            $this->getObject('com:easydoc.database.table.folders')->getDriver()->delete($query);
        }

        // Add new files
        if (count($insert))
        {
            $query = $this->getObject('database.query.insert')
                          ->table('easydoc_folders')
                          ->columns(['folder', 'name', 'modified_on']);

            $query_count = 0;

            for ($i = 0, $count = count($insert); $i < $count; $i++)
            {
                $file = $insert[$i];

                $query->values(array_merge($this->_splitPath($file), [$this->_getModifiedTime($path.'/'.$file)]));

                $query_count++;

                if ($query_count == 100 || $i == $count-1)
                {
                    $once = 1;
                    $string = str_replace('INSERT', 'INSERT IGNORE', $query->toString(), $once);

                    $this->getObject('lib:database.driver.mysqli')->execute($string);

                    $query->values = [];
                    $query_count = 0;
                }
            }
        }
    }

    protected function _getModifiedTime($path)
    {
        $modified = @filemtime($path);

        if ($modified) {
            $modified = gmdate('Y-m-d H:i:s', $modified);
        }

        return $modified;
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

    public function getFolderList()
    {
        $query = $this->getObject('database.query.select')
                               ->columns(['easydoc_folder_id', 'path' => 'CONCAT_WS("/", NULLIF(folder, ""), name)'])
                               ->table('easydoc_folders');

        $result = $this->getObject('database.driver.mysqli')->select($query, Library\Database::FETCH_OBJECT_LIST);

        $array = [];

        foreach ($result as $row) {
            $array[$row->path] = $row->easydoc_folder_id;
        }

        return $array;
    }

    public function getFileList()
    {
        $query = $this->getObject('database.query.select')
                      ->columns(['easydoc_file_id', 'path' => 'CONCAT_WS("/", NULLIF(folder, ""), name)'])
                      ->table('easydoc_files');

        $result = $this->getObject('database.driver.mysqli')->select($query, Library\Database::FETCH_OBJECT_LIST);

        $array = [];

        foreach ($result as $row) {
            $array[$row->path] = $row->easydoc_file_id;
        }

        return $array;
    }

    public function getOrphanFiles($mode = Library\Database::FETCH_FIELD_LIST, $callback = null)
    {
        /** @var Library\DatabaseQuerySelect $query */
        $query = $this->getObject('database.query.select');

        $query->columns(['path' => 'TRIM(LEADING "/" FROM CONCAT_WS("/", tbl.folder, tbl.name))'])
              ->table(['tbl' => 'easydoc_files'])
              ->join(['d' => 'easydoc_documents'], 'd.storage_path = TRIM(LEADING "/" FROM CONCAT_WS("/", tbl.folder, tbl.name))')
              ->where('d.easydoc_document_id IS  NULL')
              ->order('path');

        if (is_callable($callback)) {
            call_user_func($callback, $query);
        }

        $results = $this->getObject('com:easydoc.database.table.files')->select($query, $mode);

        return $results;
    }

    public function getOrphanFolders($mode = Library\Database::FETCH_FIELD_LIST, $callback = null)
    {
        /** @var Library\DatabaseQuerySelect $query */
        $query = $this->getObject('database.query.select');

        $query->columns(['path' => 'TRIM(LEADING "/" FROM CONCAT_WS("/", tbl.folder, tbl.name))'])
              ->table(['tbl' => 'easydoc_folders'])
              ->join(['cf' => 'easydoc_category_folders'], 'cf.folder = TRIM(LEADING "/" FROM CONCAT_WS("/", tbl.folder, tbl.name))')
              ->where('cf.easydoc_category_id IS  NULL')
              ->order('path');

        if (is_callable($callback)) {
            call_user_func($callback, $query);
        }

        $results = $this->getObject('com:easydoc.database.table.folders')->select($query, $mode);

        return $results;
    }

    public function addFile($path, $entity = null)
    {
        list($folder, $name) = $this->_splitPath($path);

        $row = $this->getObject('com:easydoc.database.table.files')->createRow();
        $row->folder = $folder;
        $row->name   = $name;
        $row->modified_on = $entity && $entity->modified_date ? gmdate('Y-m-d H:i:s', $entity->modified_date) : null;

        return $row->save();
    }

    public function addFolder($path)
    {
        list($folder, $name) = $this->_splitPath($path);

        $table = $this->getObject('com:easydoc.database.table.folders');

        if (!$table->count(['folder' => $folder, 'name' => $name]))
        {
            $row = $table->createRow();
            $row->folder = $folder;
            $row->name   = $name;

            return $row->save();
        }

        return true;
    }
}
