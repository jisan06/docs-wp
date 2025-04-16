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
 * Taggable Controller Behavior
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package Koowa\Component\Tags
 */
class ModelBehaviorTaggable extends Library\ModelBehaviorAbstract
{
    /**
     * Constructor.
     *
     * @param Library\ObjectConfig $config Configuration options.
     */
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->addCommandCallback('before.create' , '_makeTaggable');
        $this->addCommandCallback('before.fetch'  , '_makeTaggable');
        $this->addCommandCallback('before.count'  , '_makeTaggable');
    }

    /**
     * High priority is used so that the table object is made taggable before paginatable kicks in
     *
     * @param Library\ObjectConfig $config
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'priority' => Library\BehaviorAbstract::PRIORITY_HIGH,
            'strict'   => false
        ]);

        parent::_initialize($config);
    }

    /**
     * Insert the model states
     *
     * @param Library\ObjectMixable $mixer
     */
    public function onMixin(Library\ObjectMixable $mixer)
    {
        parent::onMixin($mixer);

        //Insert the tag model state
        $mixer->getState()->insert('tag', 'slug');
    }

    /**
     * Make the model entity taggable
     *
     * @param Library\ModelContextInterface $context
     */
    protected function _makeTaggable(Library\ModelContextInterface $context)
    {
        $model = $context->getSubject();

        $model->getTable()->addBehavior('com:tags.database.behavior.taggable', [
            'tags'   => $model->getState()->tag,
            'strict' => $this->getConfig()->strict
        ]);
    }

    /**
     * Bind the tag to query
     *
     * @param   Library\ModelContextInterface $context A model context object
     * @return  void
     */
    protected function _beforeFetch(Library\ModelContextInterface $context)
    {
        $model = $context->getSubject();

        if ($model instanceof Library\ModelDatabase)
        {
            $state = $context->state;

            if ($state->tag) {
                $context->query->bind(['tag' => $state->tag]);
            }
        }
    }

    /**
     * Bind the tag to query
     * @param Library\ModelContextInterface $context
     */
    protected function _beforeCount(Library\ModelContextInterface $context)
    {
        $this->_beforeFetch($context);
    }
}
