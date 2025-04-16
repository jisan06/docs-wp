<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelStorages extends Library\ModelAbstract
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->insert('container'   , 'identifier', '')
            ->insert('storage_type', 'identifier', 'file')
            ->insert('storage_path', 'raw', '');
    }

    protected function _actionFetch(Library\ModelContext $context)
    {
        $state = $this->getState();

        if ($state->storage_type == 'file')
        {
            // Can't use basename as it gets rid of UTF characters at the beginning of the file name
            $folder = dirname($state->storage_path) !== '.' ? dirname($state->storage_path) : '';
            $name   = \Foliokit\basename($state->storage_path);

            $entity = $this->getObject('com:files.model.entity.file', [
                'data' => [
                    'scheme'    => 'file',
                    'container' => $state->container,
                    'folder' 	=> $folder,
                    'name' 		=> $name
                ]
            ]);
        }
        else
        {
            $entity = $this->getObject('com:easydoc.model.entity.remote', [
                'data' => [
                    'path' => $state->storage_path
                ]
            ]);
        }

        return $entity;
    }
}
