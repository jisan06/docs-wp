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
 * Column Database Schema
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Database\Schema
 */
class DatabaseSchemaColumn
{
    /**
     * Column name
     *
     * @var string
     */
    public $name;

    /**
     * Column type
     *
     * @var	string
     */
    public $type;

    /**
     * Column length
     *
     * @var integer
     */
    public $length;

    /**
     * Column scope
     *
     * @var string
     */
    public $scope;

    /**
     * Column default value
     *
     * @var string
     */
    public $default;

    /**
     * Required column
     *
     * @var bool
     */
    public $required = false;

    /**
     * Is the column a primary key
     *
     * @var bool
     */
    public $primary = false;

    /**
     * Is the column autoincremented
     *
     * @var	bool
     */
    public $autoinc = false;

    /**
     * Is the column unique
     *
     * @var	bool
     */
    public $unique = false;

    /**
     * Related index columns
     *
     * @var	bool
     */
    public $related = array();

    /**
     * Filter
     *
     * @var	string
     */
    public $filter;
}
