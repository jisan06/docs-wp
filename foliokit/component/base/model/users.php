<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Library;

/**
 * Users model that wraps WordPress user data
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Base
 */
class ModelUsers extends Library\ModelDatabase
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->insert('email'   , 'email', null, true)
            ->insert('username', 'alnum', null, true)
            ->insert('nickname', 'alnum', null, true);
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'table'     => $this->getIdentifier()->name,
            'behaviors' => ['searchable' => ['columns' => ['user_login', 'user_nicename', 'user_email', 'display_name']]]
        ]);

        parent::_initialize($config);
    }

    /**
     * Always use the base site prefix for the users table in multi site installations
     *
     * #__users table is shared between sites on a network. Therefore we need to rewrite the query to go to the
     * base site on users table queries.
     *
     * Doing this via cloning the DB driver and calling DatabaseDriver::setTablePrefix with the base prefix doesn't work
     * as DatabaseQueryShow::__toString calls the singleton DB driver which has the site prefix.
     *
     * @param mixed $table
     * @return ModelUsers
     */
    public function setTable($table)
    {
        // Swap site-specific prefix for base prefix in multisite installations for users table
        
        if (function_exists('is_multisite') && is_multisite())
        {
            global $wpdb;

            $driver = $this->getObject('database');
            $driver->addCommandCallback('before.select', function($context) use ($wpdb) {
                $query = (string) $context->query;

                if (strpos($query, $wpdb->prefix.'users') !== false) {
                    $query = str_replace($wpdb->prefix.'users', $wpdb->base_prefix.'users', $query);
                }

                $context->query = $query;
            });
        }

        return parent::setTable($table);
    }
}