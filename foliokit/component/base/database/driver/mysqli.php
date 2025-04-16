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
use EasyDocLabs\WP;

/**
 * MySQLi Database Driver
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class DatabaseDriverMysqli extends Library\DatabaseDriverMysqli implements Library\ObjectMultiton
{
    /**
     * 
     * @var wpdb
     */
    protected $_connection;
    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   Library\ObjectConfig $config Configuration options.
     * @return  void
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $this->_connection = WP::global('wpdb');

        //Set the table prefix
        $config->append([
            'table_prefix'  => $this->_connection->prefix,
            'table_needle'  => '#__',
        ]);

        parent::_initialize($config);
    }

    public function connect()
    {
        $this->_connected  = true;

        return $this;
    }

    public function disconnect()
    {
        return $this;
    }

    public function isConnected()
    {
        return true;
    }

    /**
     * 
     * @return wpdb
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    public function setConnection($resource)
    {
        $this->_connection = $resource;

        return $this;
    }

    public function setDatabase($database)
    {
        $this->_database = $database;

        return $this;
    }

    public function execute($query, $mode = Library\Database::RESULT_STORE)
    {
        if ($mode == Library\Database::MULTI_QUERY) {
            $queries = $this->splitQueries($query);
            $result = false;

            foreach ($queries as $query) {
                $result = $this->execute($query);
            }

            return $result;
        }

        $wpdb = $this->getConnection();
        
        // Add or replace the database table prefix.
        if (!($query instanceof Library\DatabaseQueryInterface)) {
            $query = $this->replaceTableNeedle($query);
        }

        $result = $wpdb->query((string)$query);

        $this->_affected_rows = $wpdb->rows_affected;
        $this->_insert_id = $wpdb->insert_id;

        return $result;
    }

    public function select(Library\DatabaseQueryInterface $query, $mode = Library\Database::FETCH_ARRAY_LIST, $key = '')
    {
        if (!$query instanceof Library\DatabaseQuerySelect && !$query instanceof Library\DatabaseQueryShow) {
            throw new \InvalidArgumentException('Query must be an instance of DatabaseQuerySelect or DatabaseQueryShow');
        }

        $wpdb = $this->getConnection();

        $context  = $this->getContext();
        $context->query = $query;
        $context->mode  = $mode;

        // Execute the insert operation
        if ($this->invokeCommand('before.select', $context) !== false)
        {
            if (!($context->query instanceof Library\DatabaseQueryInterface)) {
                $context->query = $this->replaceTableNeedle($context->query);
            }

            $query = (string)$context->query;

            switch ($context->mode)
                {
                    case Library\Database::FETCH_ROW         :
                    case Library\Database::FETCH_ARRAY       :
                        $context->result = $wpdb->get_row($query, ARRAY_A);
                        break;

                    case Library\Database::FETCH_ROWSET      :
                    case Library\Database::FETCH_ARRAY_LIST  :
                        $result = $wpdb->get_results($query);
                        $new_array = [];

                        if ($result) {
                            foreach ( $result as $row ) {
                                if ($key) {
                                    $new_array[ $row->$key ] = (array)$row;
                                } else {
                                    $new_array[] = (array)$row;
                                }
                            }
                        }

                        $context->result = $new_array;
                        break;

                    case Library\Database::FETCH_FIELD       :
                        $context->result = $wpdb->get_var($query, 0, $key ?: 0);
                        break;

                    case Library\Database::FETCH_FIELD_LIST  :
                        $context->result = $wpdb->get_col($query, $key ?: 0);
                        break;

                    case Library\Database::FETCH_OBJECT      :
                        $context->result = $wpdb->get_row($query);
                        break;

                    case Library\Database::FETCH_OBJECT_LIST :
                        $result = $wpdb->get_results($query);
                        $new_array = [];

                        if ($result) {
                            foreach ( $result as $row ) {
                                if ($key) {
                                    $new_array[ $row->$key ] = $row;
                                } else {
                                    $new_array[] = $row;
                                }
                            }
                        }

                        $context->result = $new_array;
                        break;

                    default :
                        break;
                }

            $this->invokeCommand('after.select', $context);
        }

        return Library\ObjectConfig::unbox($context->result);
    }

    protected function _quoteValue($value)
    {
        $wpdb = $this->getConnection();

        $value =  '\''.$wpdb->_real_escape($value).'\'';
        return $value;
    }

    public function splitQueries($sql)
    {
        $start = 0;
        $open = false;
        $comment = false;
        $endString = '';
        $end = strlen($sql);
        $queries = [];
        $query = '';
    
        for ($i = 0; $i < $end; $i++)
        {
            $current = substr($sql, $i, 1);
            $current2 = substr($sql, $i, 2);
            $current3 = substr($sql, $i, 3);
            $lenEndString = strlen($endString);
            $testEnd = substr($sql, $i, $lenEndString);
    
            if ($current == '"' || $current == "'" || $current2 == '--'
                || ($current2 == '/*' && $current3 != '/*!' && $current3 != '/*+')
                || ($current == '#' && $current3 != '#__')
                || ($comment && $testEnd == $endString))
            {
                // Check if quoted with previous backslash
                $n = 2;
    
                while (substr($sql, $i - $n + 1, 1) == '\\' && $n < $i)
                {
                    $n++;
                }
    
                // Not quoted
                if ($n % 2 == 0)
                {
                    if ($open)
                    {
                        if ($testEnd == $endString)
                        {
                            if ($comment)
                            {
                                $comment = false;
                                if ($lenEndString > 1)
                                {
                                    $i += ($lenEndString - 1);
                                    $current = substr($sql, $i, 1);
                                }
                                $start = $i + 1;
                            }
                            $open = false;
                            $endString = '';
                        }
                    }
                    else
                    {
                        $open = true;
                        if ($current2 == '--')
                        {
                            $endString = "\n";
                            $comment = true;
                        }
                        elseif ($current2 == '/*')
                        {
                            $endString = '*/';
                            $comment = true;
                        }
                        elseif ($current == '#')
                        {
                            $endString = "\n";
                            $comment = true;
                        }
                        else
                        {
                            $endString = $current;
                        }
                        if ($comment && $start < $i)
                        {
                            $query = $query . substr($sql, $start, ($i - $start));
                        }
                    }
                }
            }
    
            if ($comment)
            {
                $start = $i + 1;
            }
    
            if (($current == ';' && !$open) || $i == $end - 1)
            {
                if ($start <= $i)
                {
                    $query = $query . substr($sql, $start, ($i - $start + 1));
                }
                $query = trim($query);
    
                if ($query)
                {
                    if (($i == $end - 1) && ($current != ';'))
                    {
                        $query = $query . ';';
                    }
                    $queries[] = $query;
                }
    
                $query = '';
                $start = $i + 1;
            }
        }
    
        return $queries;
    }
}