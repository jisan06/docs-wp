<?php
/**
 * @package    EasyDocs
 * @copyright   Copyright (C) 2011 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;
use EasyDocLabs\Component\Scheduler;
use EasyDocLabs\Library;
use EasyDocLabs\WP;

class JobCategories extends Scheduler\JobAbstract
{
    const NO_CATEGORY = -1;

    const NO_OWNER = -2;

    const NO_FOLDER = -3;

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(array(
            'frequency' => Scheduler\JobInterface::FREQUENCY_EVERY_FIVE_MINUTES
        ));

        parent::_initialize($config);
    }

    public function run(Scheduler\JobContextInterface $context)
    {
        if (!$this->getObject('com:easydoc.model.entity.config')->automatic_category_creation) {
            $context->log('Automatic category creation is turned off in global configuration');

            return $this->skip();
        }

        $state = $context->getState();
        $queue = Library\ObjectConfig::unbox($state->queue);
        $skip  = Library\ObjectConfig::unbox($state->skip);

        if (!$queue) $queue = [];
        if (!$skip)  $skip = [];

        $context->log(count($queue).' folders in the queue');

        if (is_array($queue))
        {
            $limit = 5; // only create 5 documents per run to limit memory errors
            
            while ($context->hasTimeLeft() && count($queue) && $limit)
            {
                $path = array_shift($queue);

                $context->log('Creating category for the path '.$path);

                $result = $this->_createCategory($path);

                if ($result !== true) {
                    $skip[] = $path;

                    $context->log('Adding to skip list: '.$path);
                }
                
                if ($result === false) {
                    $context->log('Failed to create category for '.$path);
                }
                else if ($result === static::NO_FOLDER) {
                    $context->log('Folder is missing in the filesystem: '.$path);
                }
                else if ($result === static::NO_CATEGORY) {
                    $context->log('No category selected for the parent of '.$path);
                }
                else if ($result === static::NO_OWNER) {
                    $context->log('No default owner selected in global configuration');
                } else {
                    $context->log('Created category for the path '.$path);
                }

                $limit--;
            }
        }

        if (empty($queue) && $context->hasTimeLeft())
        {
            $behavior = $this->getObject('com:easydoc.controller.behavior.syncable');
            $behavior->syncFolders();

            /*
             * Add folders to the queue if and only if:
             * 1- It is not attached to a category
             * 2- There is not a document linking to the folder. (this makes sure we don't break existing category structures)
             * 3- It is not the tmp folder
             * 4- The parent folder has a category linked to it
             */
            $queue = $behavior->getOrphanFolders(Library\Database::FETCH_FIELD_LIST, function($query) {
                $query->limit(100);
                $query->join(array('d' => 'easydoc_documents'), 'd.storage_path LIKE CONCAT(TRIM(LEADING "/" FROM CONCAT_WS("/", `tbl`.`folder`, `tbl`.`name`)), \'/%\')');
                $query->where('d.easydoc_document_id IS NULL');

                // Skip tmp folder on root
                $query->where('(tbl.name <> :tmp OR tbl.folder <> :empty)')->bind([
                    'tmp' => ControllerBehaviorMovable::TEMP_FOLDER,
                    'empty' => ''
                ]);

                // Skip folders with no parents
                $query->join(['parent_folder' => 'easydoc_category_folders'], 'parent_folder.folder = tbl.folder');
                $query->where('(parent_folder.folder IS NOT NULL OR tbl.folder = :empty)');
            });

            $queue = array_diff($queue, $skip);

            $context->log(sprintf('Added %s orphans to the queue', count($queue)));
        }

        $state->queue = (array) $queue;
        $state->skip = (array) $skip;

        return empty($queue) ? $this->complete() : $this->suspend();
    }

    protected function _getOwner()
    {
        $owner = $this->getObject('com:easydoc.model.entity.config')->default_owner;

        if (!$owner)
        {
            $admins = WP::get_super_admins();

            foreach ($admins as $admin)
            {
                $user = WP::get_user_by('slug', $admin);
            
                if ($user && ($owner = $user->ID)) break;
            }

            if (!$owner) $owner = static::NO_OWNER;
        }

        return $owner;
    }

    /**
     * Checks if the physical folder is still there
     *
     * @param string $path
     * @return bool
     */
    protected function _folderExists($path)
    {
        $basepath = $this->getObject('com:files.model.containers')->slug('easydoc-files')->fetch()->fullpath;

        return !empty($path) && is_dir($basepath.'/'.$path);
    }

    protected function _createCategory($path)
    {
        list($folder, $name) = $this->_splitPath($path);

        if (!$this->_folderExists($path)) {
            return static::NO_FOLDER;
        }

        if ($this->getObject('com:easydoc.model.categories')->folder($path)->count()) {
            return true;
        }

        if ($folder)
        {
            $category = $this->getObject('com:easydoc.model.categories')->folder($folder)->fetch();

            if (count($category) !== 1 || $category->isNew()) {
                return static::NO_CATEGORY;
            }

            $parent_id  = $category->id;
            $created_by = $category->created_by;
        }
        else
        {
            $parent_id  = null;
            $created_by = $this->_getOwner();
        }

        if ($this->getObject('com:easydoc.model.entity.config')->automatic_humanized_titles)
            {
            $title = $this->getObject('com:easydoc.template.helper.string')->humanize(array(
                'string' => $name,
                'strip_extension' => false
            ));
        } else {
            $title = $name;
        }

        $details = array(
            'title' => $title,
            'created_by' => $created_by,
            'parent_id' => $parent_id,
            'folder' => $path,
            'automatic_folder' => 1
        );

        $result = $this->getObject('com://admin/easydoc.controller.category', array(
            'request'   => $this->getObject('request'),
            'behaviors' => array(
                'permissible' => array(
                    'permission' => 'com:easydoc.controller.permission.yesman'
                )
            )
        ))->add($details);

        return $result instanceof Library\DatabaseRowInterface ? !$result->isNew() : false;
    }
    
    protected function _splitPath($path)
    {
        $folder = pathinfo($path, PATHINFO_DIRNAME);
        $name   = \Foliokit\basename($path);

        if ($folder === '.') {
            $folder = '';
        }

        return array($folder, $name);
    }
}