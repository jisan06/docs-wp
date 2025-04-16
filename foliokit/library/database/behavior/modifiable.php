<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Library;

/**
 * Modifiable Database Behavior
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Database\Behavior
 */
class DatabaseBehaviorModifiable extends DatabaseBehaviorAbstract
{
    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param ObjectConfig $config 	An optional ObjectConfig object with configuration options
     * @return void
     */
    protected function _initialize(ObjectConfig $config)
    {
        $config->append(array(
            'priority'  => self::PRIORITY_LOW,
        ));

        parent::_initialize($config);
    }

    /**
     * Get the user that last edited the resource
     *
     * @return UserInterface Returns a User object
     */
    public function getEditor()
    {
        return $this->getObject('user.provider')->getUser($this->modified_by);
    }

    /**
     * Check if the behavior is supported
     *
     * Behavior requires a 'modified_by' or 'modified_by' row property
     *
     * @return  boolean  True on success, false otherwise
     */
    public function isSupported()
    {
        $table = $this->getMixer();

        //Only check if we are connected with a table object, otherwise just return true.
        if($table instanceof DatabaseTableInterface)
        {
            if(!$table->hasColumn('modified_by') && !$table->hasColumn('modified_on')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Set modified information
     *
     * Requires an 'modified_on' and 'modified_by' column
     *
     * @param DatabaseContext	$context A database context object
     * @return void
     */
    protected function _beforeInsert(DatabaseContext $context)
    {
        if($this->hasProperty('modified_by')) {
            $this->modified_by = (int) $this->getObject('user')->getId();
        }

        if($this->hasProperty('modified_on')) {
            $this->modified_on = gmdate('Y-m-d H:i:s');
        }
    }

    /**
     * Set modified information
     *
     * Requires a 'modified_on' and 'modified_by' column
     *
     * @param DatabaseContext	$context A database context object
     * @return void
     */
    protected function _beforeUpdate(DatabaseContext $context)
    {
        //Get the modified columns
        $modified  = $this->getTable()->filter($this->getProperties(true));

        if(!empty($modified)) {
            $this->_beforeInsert($context);
        }
    }

    /**
     * Set created information
     *
     * Requires a 'created_by' column
     *
     * @param DatabaseContext	$context A database context object
     * @return void
     */
    protected function _afterSelect(DatabaseContext $context)
    {
        $rowset = $context->data;

        if($rowset instanceof DatabaseRowsetInterface)
        {
            $users = array();

            foreach($rowset as $row)
            {
                if(!empty($row->modified_by)) {
                    $users[] = $row->modified_by;
                }
            }

            //Lazy load the users
            $this->getObject('user.provider')->fetch($users, true);
        }
    }
}
