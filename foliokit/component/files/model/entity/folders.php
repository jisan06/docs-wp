<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Files;

use EasyDocLabs\Library;

/**
 * Folders Entity
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ModelEntityFolders extends ModelEntityNodes
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'identity_key' => 'path'
        ]);

        parent::_initialize($config);
    }

	/**
     * Adds the rows as a hierachical tree of nodes.
     *
     * {@inheritdoc}
     */
    public function create(array $properties = [], $status = null)
    {
        $entity = $this->getObject('com:files.model.entity.folder');
        $entity->setProperties($properties);

        $this->insert($entity);

        $hierarchy = $entity->hierarchy;

        if(!empty($hierarchy) && is_array($entity->hierarchy))
        {
            // We are gonna add it as a child of another node
            $this->remove($entity);

            $nodes   = $this;
            $node    = null;

            foreach($hierarchy as $key => $parent)
            {
                $path = implode('/', array_slice($hierarchy, 0, $key+1));

                if ($node) {
                    $nodes = $node->getChildren();
                }

                $node = $nodes->find(['path' => $path]);
            }

            if (!$node) {
                $this->insert($entity);
            } else {
                $node->insertChild($entity);
            }
        }

        $entity->removeProperty('hierarchy');

        return $entity;
    }

    public function toArray()
    {
        $data = [];

        foreach ($this as $row) {
            $data[] = $row->toArray();
        }

        return $data;
    }
}
