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
 * Table Database Schema
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Database\Schema
 */
class DatabaseSchemaTable
{
    /**
     * Table name
     *
     * @var string
     */
    public $name;

    /**
     * The storage engine
     *
     * @var string
     */
    public $engine;

    /**
     * Table type
     *
     * @var	string
     */
    public $type;

    /**
     * Table length
     *
     * @var integer
     */
    public $length;

    /**
     * Table next auto increment value
     *
     * @var integer
     */
    public $autoinc;

    /**
     * The tables character set and collation
     *
     * @var string
     */
    public $collation;

    /**
     * The tables description
     *
     * @var string
     */
    public $description;

    /**
     * When the table was last updated
     *
     * @var timestamp
     */
    public $modified;

    /**
     * List of columns
     *
     * Associative array of columns, where key holds the columns name and the value is an DatabaseSchemaColumn
     * object.
     *
     * @var	array
     */
    public $columns = array();

    /**
     * List of behaviors
     *
     * Associative array of behaviors, where key holds the behavior identifier string and the value is an
     * DatabaseBehavior object.
     *
     * @var	array
     */
    public $behaviors = array();

    /**
     * List of indexes
     *
     * Associative array of indexes, where key holds the index name and the and the value is an object.
     *
     * @var	array
     */
    public $indexes = array();
}
