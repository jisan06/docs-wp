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
 * Taggable Database Behavior
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package Koowa\Component\Tags
 */
class DatabaseBehaviorTaggable extends Library\DatabaseBehaviorAbstract
{
    protected $_column;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_column = $config->column;
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['strict' => false, 'column' => 'tbl.uuid']);

        parent::_initialize($config);
    }

    /**
     * Get a list of tags
     *
     * @return Library\DatabaseRowsetInterface
     */
    public function getTags()
    {
        $package = $this->getMixer()->getIdentifier()->package;
        $model   = $this->getObject('com:tags.model.tags', ['table' => $package.'_tags']);

        if(!$this->isNew()) {
            $tags = $model->row($this->uuid)->fetch();
        } else {
            $tags = $model->fetch();
        }

        return $tags;
    }

    /**
     * Modify the select query
     *
     * If the query's where information includes a tag property, auto-join the tags tables with the query and select
     * all the rows that are tagged with a term.
     */
    protected function _beforeSelect(Library\DatabaseContext $context)
    {
        $query = $context->query;
        if($context->query->params->has('tag'))
        {
            $package = $this->getMixer()->getIdentifier()->package;

            if ($this->getConfig()->strict)
            {
                $tags = Library\ObjectConfig::unbox($this->getConfig()->tags);

                for ($i = 0; $i < count($tags); $i++) {
                    $query->join("{$package}_tags_relations AS tags_relations{$i}", "tags_relations{$i}.row = {$this->_column}")
                          ->join("{$package}_tags AS tags{$i}", "tags{$i}.tag_id = tags_relations{$i}.tag_id", 'INNER')
                          ->where("tags{$i}.slug = :tag{$i}")
                          ->bind(["tag{$i}" => $tags[$i]]);
                }
            }
            else $query->join($package . '_tags_relations AS tags_relations', 'tags_relations.row = ' . $this->_column)
                      ->join($package . '_tags AS tags', 'tags.tag_id = tags_relations.tag_id')
                      ->where('tags.slug IN :tag');
        }
    }
}
