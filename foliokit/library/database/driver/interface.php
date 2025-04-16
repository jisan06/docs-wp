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
 * Database Driver Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Database\Driver
 */
interface DatabaseDriverInterface
{
    /**
     * Connect to the db
     *
     * @return  DatabaseDriverAbstract
     */
    public function connect();

    /**
     * Reconnect to the db
     *
     * @return  DatabaseDriverAbstract
     */
    public function reconnect();

    /**
     * Disconnect from db
     *
     * @return  DatabaseDriverAbstract
     */
    public function disconnect();

    /**
     * Get the connection
     *
     * Provides access to the underlying database connection. Useful for when you need to call a proprietary method
     * on the database driver
     *
     * @return resource
     */
    public function getConnection();

    /**
     * Set the connection
     *
     * @param 	resource 	$resource The connection resource
     * @return  DatabaseDriverAbstract
     */
    public function setConnection($resource);

    /**
     * Determines if the connection to the server is active.
     *
     * @return      boolean
     */
    public function isConnected();

    /**
     * Get the insert id of the last insert operation
     *
     * @return mixed The id of the last inserted row(s)
     */
    public function getInsertId();

    /**
     * Retrieves the column schema information about the given table
     *
     * @param 	string 	$table A table name
     * @return	DatabaseSchemaTable
     */
    public function getTableSchema($table);

    /**
     * Lock a table.
     *
     * @param  string  $table The name of the table
     * @return boolean True on success, false otherwise.
     */
    public function lockTable($table);

    /**
     * Unlock a table.
     *
     * @return boolean True on success, false otherwise.
     */
    public function unlockTable();

    /**
     * Perform a select query.
     *
     * @param   DatabaseQueryInterface  $query A full SQL query to run. Data inside the query should be properly escaped.
     * @param   integer $mode   The result mode, either the constant Database::RESULT_USE or Database::RESULT_STORE
     *                          depending on the desired behavior. By default, Database::RESULT_STORE is used. If you
     *                          use Database::RESULT_USE all subsequent calls will return error Commands out of sync
     *                          unless you free the result first.
     * @param   string $key  The column name of the index to use.
     * @return  mixed If successful returns a result object otherwise FALSE
     */
    public function select(DatabaseQueryInterface $query, $mode = Database::RESULT_STORE, $key = '');

    /**
     * Insert a row of data into a table.
     *
     * @param DatabaseQueryInsert $query The query object.
     * @return bool|integer  If the insert query was executed returns the number of rows updated, or 0 if
     *                       no rows where updated, or -1 if an error occurred. Otherwise FALSE.
     */
    public function insert(DatabaseQueryInsert $query);

    /**
     * Update a table with specified data.
     *
     * @param  DatabaseQueryUpdate $query The query object.
     * @return integer  If the update query was executed returns the number of rows updated, or 0 if
     *                  no rows where updated, or -1 if an error occurred. Otherwise FALSE.
    */
    public function update(DatabaseQueryUpdate $query);

    /**
     * Delete rows from the table.
     *
     * @param  DatabaseQueryDelete $query The query object.
     * @return integer 	Number of rows affected, or -1 if an error occurred.
    */
    public function delete(DatabaseQueryDelete $query);

    /**
     * Use and other queries that don't return rows
     *
     * @param  string   $sql  The query to run. Data inside the query should be properly escaped.
     * @param  integer  $mode The result made, either the constant Database::RESULT_USE or Database::RESULT_STORE
     *                  depending on the desired behavior. By default, Database::RESULT_STORE is used. If you
     *                  use Database::RESULT_USE all subsequent calls will return error Commands out of sync
     *                  unless you free the result first.
     * @throws DatabaseException If the query could not be executed
     * @return boolean  For SELECT, SHOW, DESCRIBE or EXPLAIN will return a result object.
     *                  For other successful queries  return TRUE.
    */
    public function execute($sql, $mode = Database::RESULT_STORE );

    /**
     * Set the table prefix
     *
     * @param string $prefix The table prefix
     * @return DatabaseDriverAbstract
     * @see DatabaseDriverAbstract::replaceTableNeedle
     */
    public function setTablePrefix($prefix);

    /**
     * Get the table prefix
     *
     * @return string The table prefix
     * @see DatabaseDriverAbstract::replaceTableNeedle
     */
    public function getTablePrefix();

    /**
     * Get the table needle
     *
     * @return string The table needle
     * @see DatabaseDriverAbstract::replaceTableNeedle
     */
    public function getTableNeedle();

    /**
     * This function replaces the table needles in a query string with the actual table prefix.
     *
     * @param  string 	$sql The SQL query string
     * @return string	The SQL query string
     */
    public function replaceTableNeedle( $sql );

    /**
     * Safely quotes a value for an SQL statement.
     *
     * If an array is passed as the value, the array values are quoted and then returned as a comma-separated string;
     * this is useful for generating IN() lists.
     *
     * @param   mixed $value The value to quote.
     * @return string An SQL-safe quoted value (or a string of separated-
     *                and-quoted values).
     */
    public function quoteValue($value);

    /**
     * Quotes a single identifier name (table, table alias, table column, index, sequence).  Ignores empty values.
     *
     * This function requires all SQL statements, operators and functions to be uppercased.
     *
     * @param string|array The identifier name to quote.  If an array, quotes each element in the array as an
     *                      identifier name.
     * @return string|array The quoted identifier name (or array of names).
     */
    public function quoteIdentifier($spec);

    /**
     * Returns a query object with the current adapter set
     *
     * @param string|ObjectIdentifier $identifier Query type (e.g. `select`, `insert`) or a full identifier
     * @return DatabaseQueryInterface
     */
    public function getQuery($identifier);
}
