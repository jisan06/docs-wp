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

class JobDocuments extends Scheduler\JobAbstract
{
    const NO_CATEGORY = -1;

    const NO_FILE = -2;

    const HAS_VERSION = -3;

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(array(
            'frequency' => Scheduler\JobInterface::FREQUENCY_EVERY_FIVE_MINUTES
        ));

        parent::_initialize($config);
    }

    public function run(Scheduler\JobContextInterface $context)
    {
        if (!$this->getObject('com:easydoc.model.entity.config')->automatic_document_creation) {
            $context->log('Automatic document creation is turned off in global configuration');

            return $this->skip();
        }

        $state = $context->getState();
        $queue = Library\ObjectConfig::unbox($state->queue);
        $skip  = Library\ObjectConfig::unbox($state->skip);

        if (!$queue) $queue = [];
        if (!$skip)  $skip = [];

        $context->log(count($queue).' files in the queue');

        if (is_array($queue))
        {
            $limit = 5; // only create 5 documents per run to limit memory errors
            while ($context->hasTimeLeft() && count($queue) && $limit)
            {
                $path = array_shift($queue);

                $context->log('Creating document for the path '.$path);

                $result = $this->_createDocument($path);

                if ($result !== true) {
                    $skip[] = $path;

                    $context->log('Adding to skip list: '.$path);
                }

                if ($result === false) {
                    $context->log('Failed to create document for '.$path);
                }
                else if ($result === static::HAS_VERSION) {
                    $context->log('File has an older version in the filesystem: '.$path);
                }
                else if ($result === static::NO_FILE) {
                    $context->log('File is missing in the filesystem: '.$path);
                }
                else if ($result === static::NO_CATEGORY) {
                    $context->log('No category selected for '.$path);
                } else {
                    $context->log('Created document for the path '.$path);
                }

                $limit--;
            }
        }

        if (empty($queue) && $context->hasTimeLeft()) {
            $behavior = $this->getObject('com:easydoc.controller.behavior.syncable');
            $behavior->syncFolders();
            $behavior->syncFiles();
            $queue = $behavior->getOrphanFiles(Library\Database::FETCH_FIELD_LIST, function($query) {
                $query->limit(100);
                $query->join(array('cf' => 'easydoc_category_folders'), 'cf.folder = tbl.folder')
                      ->where('cf.folder IS NOT NULL');
                $query->where('tbl.folder <> \'\'');
                $query->where('tbl.folder <> :tmp')->bind(['tmp' => ControllerBehaviorMovable::TEMP_FOLDER]);
            });

            $queue = array_diff($queue, $skip);

            $context->log(sprintf('Added %s orphans to the queue', count($queue)));
        }

        $state->queue = (array) $queue;
        $state->skip = (array) $skip;

        return empty($queue) ? $this->complete() : $this->suspend();
    }

    /**
     * Checks if the physical file is still there
     *
     * @param string $path
     * @return bool
     */
    protected function _fileExists($path)
    {
        $basepath = $this->getObject('com:files.model.containers')->slug('easydoc-files')->fetch()->fullpath;

        return !empty($path) && file_exists($basepath.'/'.$path);
    }

    protected function _createDocument($path)
    {
        list($folder, $name) = $this->_splitPath($path);

        if (!$this->_fileExists($path)) {
            return static::NO_FILE;
        }

        // A document has been created for the file already
        if ($this->getObject('com:easydoc.model.documents')->storage_path($path)->count()) {
            return true;
        }

        $extension = pathinfo($name, PATHINFO_EXTENSION);
        // if the name is a revision like foo (1).txt and foo.txt exists and already has a document
        if (preg_match("#\(\d+\)\.$extension$#i", $name)) {
            $canonical_path = preg_replace("#( \(\d+\)\.$extension)$#i", '.'.$extension, $path);

            if ($this->_fileExists($canonical_path)) {
                return static::HAS_VERSION;
            }
        }

        $category = $this->getObject('com:easydoc.model.categories')->folder($folder)->fetch();

        if (count($category) !== 1 || $category->isNew()) {
            return static::NO_CATEGORY;
        }

        if ($this->getObject('com:easydoc.model.entity.config')->automatic_humanized_titles) {
            $title = $this->getObject('com:easydoc.template.helper.string')->humanize(array(
                'string' => $name,
                'strip_extension' => true
            ));
        } else {
            $title = $name;
        }

        $details = array(
            'title' => $title,
            'created_by' => $category->created_by,
            'easydoc_category_id' => $category->id,
            'storage_type' => 'file',
            'storage_path' => $path,
            'automatic_thumbnail' => 1
        );

        $result = $this->getObject('com://admin/easydoc.controller.document', array(
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