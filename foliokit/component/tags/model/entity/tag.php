<?php
/**
 * FolioKit Tags
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Tags;

use EasyDocLabs\Library;

/**
 * Tag Model Entity
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package Koowa\Component\Tags
 */
class ModelEntityTag extends Library\ModelEntityRow
{
    /**
     * Save the tag in the database.
     *
     * If the tag already exists, only add the relationship.
     *
     * @return bool
     */
    public function save()
    {
        $result = true;

        if($this->row)
        {
            $tag = $this->getTable()->select(['title' => $this->title], Library\Database::FETCH_ROW);

            //Create the tag
            if($this->isNew() && $tag->isNew())
            {
                //Unset the row property
                $properties = $this->getProperties();
                unset($properties['row']);

                $result = $tag->setProperties($properties)->save();
            }

            //Create the tag relation
            if($result && !$tag->isNew())
            {
                $data = [
                    'tag_id' => $tag->id,
                    'row'    => $this->row,
                ];

                $name     = $this->getTable()->getName().'_relations';
                $table    = $this->getObject('com:tags.database.table.relations', ['name' => $name]);

                if (!$table->count($data)) {
                    $relation = $table->createRow(['data' => $data]);

                    $result = $table->insert($relation);
                }
            }
        }
        else
        {
            // Check if a tag with the same name exists
    
            $tag = $this->getTable()->select(['title' => $this->title], Library\Database::FETCH_ROW);
    
            if (!$tag->isNew() && $this->id != $tag->id)
            {
                $this->setStatus(Library\Database::STATUS_FAILED);
                $this->setStatusMessage($this->getObject('translator')->translate('A tag with the same name already exists'));
    
                $result = false;
            }
            else $result = parent::save();
        }

        return $result;
    }

    /**
     * Deletes the tag and it's relations form the database.
     *
     * @return bool
     */
    public function delete()
    {
        $result = true;

        $name   = $this->getTable()->getName().'_relations';
        $table  = $this->getObject('com:tags.database.table.relations', ['name' => $name]);

        if($this->row) {
            $query = ['tag_id' => $this->id, 'row' => $this->row];
        } else {
            $query = ['tag_id' => $this->id];
        }

        $rowset = $table->select($query);

        //Delete the relations
        if($rowset->count()) {
            $result = $rowset->delete();
        }

        //Delete the tag
        if(!$this->row) {
            $result = parent::delete();
        }

        return $result;
    }
}
