<?php
/**
 * @package     Foliokit Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Migrator;

use EasyDocLabs\Library;

class MigratorBehaviorImportDatabase extends Library\ControllerBehaviorAbstract
{
    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   Library\ObjectConfig $config Configuration options.
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(array(
            'import_limit' => 20,
        ));

        parent::_initialize($config);
    }

    /**
     * Query task handler.
     *
     * Runs database queries from either a string or a dump file
     *
     * @param MigratorContext $context
     *
     * @return boolean Result
     */
    protected function _actionQuery(MigratorContext $context)
    {
        $job = $context->getJob();
        $job->append($this->getConfig());

        $result = false;

        if ($job->dump)
        {
            if (!is_file($job->dump)) {
                $job->dump = \EasyDocLabs\WP\ABSPATH.'/'.$job->dump;
            }

            $result = $this->executeDumpFile($job->dump);
        }
        else if ($job->query) {
            $result = $this->executeQuery($job->query);
        }

        return $result;
    }

    /**
     * Executes a dump file.
     *
     * @param string $file The file path of the dump file.
     *
     * @return bool False if there's an error, true otherwise.
     */
    public function executeDumpFile($file)
    {
        $result = false;

        if (file_exists($file)) {
            $result = $this->executeQuery(file_get_contents($file));
        }

        return $result;
    }

    /**
     * Executes a string with multiple database queries
     *
     * @param  string $query
     * @return boolean
     */
    public function executeQuery($query)
    {
        return $this->getObject('database')->execute($query, Library\Database::MULTI_QUERY);
    }

    /**
     * Fetches table rows.
     *
     * @param string $table  The table name.
     * @param array  $config The query configuration.
     *
     * @return mixed The fetched rows.
     */
    protected function _fetch($table, $config = array())
    {
        $config = new Library\ObjectConfig($config);

        $config->append(array(
            'limit'  => 0,
            'offset' => 0,
            'where'  => array(),
            'result' => Library\Database::FETCH_ARRAY_LIST
        ));

        if (!$config->columns) {
            $config->columns = '*';
        }

        $query = $this->getObject('database')->getQuery('select')
            ->table($table)
            ->columns(Library\ObjectConfig::unbox($config->columns));

        foreach ($config->where as $column => $value) {
            $query->where("{$column} = :{$column}")->bind(array($column => $value));
        }

        if ($limit = $config->limit) {
            $query->limit($limit, $config->offset);
        }

        if ($pk = $this->_getPrimaryKey($table)) {
            $query->order($pk);
        }

        return $this->getObject('database')->select($query, $config->result);
    }

    /**
     * Copy task handler.
     *
     * Makes an identical copy of a table contents into another table.
     *
     * @param MigratorContext $context
     *
     * @return array The task output.
     */
    protected function _actionCopy(MigratorContext $context)
    {
        $job = $context->getJob();
        $job->append($this->getConfig());
        $job->append(array(
            'operation' => 'INSERT IGNORE'
        ));

        $source  = $job->source;
        $target  = $job->target;
        $columns = '*';

        // Truncate the target table before copying.
        if ($job->truncate) {
            $this->_truncateTable($target);
        }

        if ($job->skip_primary_key)
        {
            $table = $this->getObject('lib:database.table.default', array(
                'name' => $target
            ));
            $columns = array_diff(array_keys($table->getColumns()), array_keys($table->getPrimaryKey()));
        }

        $select = $this->getObject('database')->getQuery('select')->table($source)->columns($columns);
        $query  = $this->getObject('database')->getQuery('insert')->table($target)->values($select);

        if ($columns !== '*') {
            $query->columns($columns);
        }

        $count = 1;
        $query = str_replace('INSERT', $job->operation, $query->toString(), $count);

        $result = $this->getObject('database')->execute($query);

        return $result;
    }

    /**
     * Move task handler.
     *
     * Makes an identical copy of a table contents into another table.
     *
     * @param MigratorContext $context
     *
     * @return array The task output.
     */
    protected function _actionMove(MigratorContext $context)
    {
        $job = $context->getJob();
        $job->append($this->getConfig());

        $source = $job->source;
        $target = $job->target;

        $this->_dropTable($target);

        return $this->executeQuery(sprintf('RENAME TABLE #__%s TO #__%s', $source, $target));

    }

    /**
     * Reads a CSV file into an associative array
     *
     * Only use when you know beforehand that the CSV file is small in size and can fit into memory!
     *
     * @param \SplFileObject $file
     * @return array
     */
    protected function _convertCsvtoArray(\SplFileObject $file)
    {
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl(',', '"', '"');

        $headers = array();
        $data    = array();
        foreach ($file as $i => $row)
        {
            if ($i === 0) {
                $headers = str_replace("\xEF\xBB\xBF", '', $row);

                continue;
            } else {
                $data[] = array_combine($headers, $row);
            }
        }

        return $data;
    }

    /**
     * Import task handler.
     *
     * @param MigratorContext $context
     *
     * @return array The task output.
     */
    protected function _actionImport(MigratorContext $context)
    {
        $job = $context->getJob();
        $job->append($this->getConfig());
        $job->append(array(
            'data' => array(
                'offset' => (int) $this->getRequest()->getData()->offset
            )
        ));

        $source = $job->source;
        $offset = $job->data->offset;
        $table  = $job->table;
        $limit  = $job->import_limit;

        // Truncate the target table when starting an import.
        if (!$offset) {
            $this->_truncateTable($job->table);
        }

        $rows = $this->_fetch($source, array('offset' => $offset, 'limit' => $limit));

        foreach ($rows as $data)
        {
            $method = sprintf('_convert%sData', ucfirst($job->entity));

            // Use a data converter if any.
            if (method_exists($this, $method)) {
                $data = $this->$method($data);
            }

            $entity = $this->_getEntity($job->entity, $data);

            // Save the entity.
            $entity->save();
        }

        $total     = $this->_countTotal($table);
        $completed = count($rows);
        $remaining = $total - $offset - $completed;
        $offset    = $offset + $limit;

        $output = array(
            'completed' => $completed,
            'total'     => $total,
            'remaining' => $remaining,
            'offset'    => $offset
        );

        return $output;
    }

    /**
     * Import assets task handler.
     *
     * @param MigratorContext $context
     *
     * @return array The task output.
     */
    protected function _actionImport_assets(MigratorContext $context)
    {
        $job = $context->getJob();
        $job->append($this->getConfig());
        $job->append(array(
            'extension' => null,
            'target'    => 'assets',
            'source'    => 'migrator_tmp_assets'
        ));

        $sql = /** @lang text */<<<SQL
        SET @parentID := (SELECT id FROM #__%2\$s WHERE name = '%1\$s');
        SET @rgt      := (SELECT rgt FROM #__%2\$s WHERE name = '%1\$s');
        SET @lft      := (SELECT lft FROM #__%2\$s WHERE name = '%1\$s');
        SET @lftTmp   := (SELECT lft FROM #__%3\$s WHERE name = '%1\$s');
        SET @delta    := (@lft - @lftTmp);
        SET @width    := (@rgt - @lft - 1);
        SET @newWidth := (SELECT 2*(COUNT(*)-1)-@width FROM #__%3\$s WHERE name LIKE '%1\$s%%');

        # delete old assets
        DELETE FROM #__%2\$s WHERE name LIKE '%1\$s.%%';

        # make space for the new assets
        UPDATE #__%2\$s SET lft = lft + @newWidth WHERE lft > @rgt;
        UPDATE #__%2\$s SET rgt = rgt + @newWidth WHERE rgt >= @rgt;

        # update the temporary table with correct parent_id, lft, and rgt values
        UPDATE #__%3\$s SET parent_id = @parentID, lft = (lft + @delta), rgt = (rgt + @delta);

        # move the rules for the main component entry first
        UPDATE #__%2\$s SET rules = (SELECT rules FROM #__%3\$s WHERE name = '%1\$s') WHERE name = '%1\$s';

        # move the rest of the data
        REPLACE INTO #__%2\$s (parent_id, lft, rgt, level, name, title, rules)
            SELECT parent_id, lft, rgt, level, name, title, rules FROM #__%3\$s WHERE name LIKE '%1\$s.%%';

        # drop temporary table
        DROP TABLE IF EXISTS `#__%3\$s`;
SQL;

        $sql_asset_id = /** @lang text */<<<SQL
        UPDATE #__%1\$s AS tbl
        JOIN #__%4\$s AS assets ON assets.name = CONCAT('%2\$s.', tbl.%3\$s)
        SET tbl.asset_id = assets.id;

SQL;

        $sql = sprintf($sql, $job->extension, $job->target, $job->source);

        $tables = (array) Library\ObjectConfig::unbox($job->tables);

        if ($tables)
        {
            foreach ($tables as $table) {
                $sql .= sprintf($sql_asset_id, $table[0], $table[1], $table[2], $job->target);
            }
        }

        $result = $this->executeQuery($sql);

        $output = array(
            'result' => $result
        );

        return $output;
    }

    /**
     * Entity getter.
     *
     * @param string $name The name of the entity.
     * @param array  $data
     *
     * @return Library\ModelEntityInterface The entity.
     */
    protected function _getEntity($name, $data)
    {
        $name      = Library\StringInflector::pluralize($name);
        $extension = $this->getConfig()->extension;

        $model = $this->getObject(sprintf('com://admin/%s.model.%s', $extension, $name));

        return $model->create($data);
    }

    /**
     * Counts the total number of rows in a table.
     *
     * @param string $table      The table name.
     * @param array  $conditions An associative array containing conditions.
     *
     * @return int The number of rows.
     */
    protected function _countTotal($table, $conditions = array())
    {
        $query = $this->getObject('database')->getQuery('select')->table($table)->columns('COUNT(*)');

        foreach ($conditions as $column => $value) {
            $query->where(sprintf('%1\$s = :%1\$s', $column))->bind(array($column => $value));
        }

        return $this->getObject('database')->select($query, Library\Database::FETCH_FIELD);
    }

    /**
     * Truncates a table.
     *
     * @param string $table The table name to truncate.
     */
    protected function _truncateTable($table)
    {
        $wpdb = \EasyDocLabs\WP::global('wpdb');

        $adapter = $this->getObject('database');
        $queries = [
            'SET FOREIGN_KEY_CHECKS = 0',
            sprintf('TRUNCATE TABLE `%s%s`', $wpdb->prefix, $table),
            'SET FOREIGN_KEY_CHECKS = 1',
        ];

        foreach ($queries as $query) {
            $adapter->execute($query);
        }
    }

    /**
     * Drops a table.
     *
     * @param string $table The table name to truncate.
     */
    protected function _dropTable($table)
    {
        $wpdb = \EasyDocLabs\WP::global('wpdb');

        $adapter = $this->getObject('database');
        $queries = [
            'SET FOREIGN_KEY_CHECKS = 0',
            sprintf('DROP TABLE IF EXISTS `%s%s`', $wpdb->prefix, $table),
            'SET FOREIGN_KEY_CHECKS = 1',
        ];

        foreach ($queries as $query) {
            $adapter->execute($query);
        }
    }

    /**
     * Returns table primary key column name(s)
     *
     * @param $table string Table name (without prefix)
     * @return array
     */
    protected function _getPrimaryKey($table)
    {
        /** @var Library\DatabaseDriverInterface $db */
        $db     = \Foliokit::getObject('database');
        $query = /**@lang text*/"
        SELECT `COLUMN_NAME` FROM `information_schema`.`COLUMNS`
        WHERE `TABLE_SCHEMA` = DATABASE() AND `COLUMN_KEY` = 'PRI' AND `TABLE_NAME` = '%s';";

        /** @var \mysqli_result $result */
        $result = $db->execute(sprintf($query, $db->getTablePrefix().$table), Library\Database::RESULT_USE);
        $keys  = array();

        while ($row = $result->fetch_row()) {
            $keys[] = $row[0];
        }

        $result->free();

        return $keys;
    }
}
