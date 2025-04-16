<?php
/**
 * @package     Foliokit Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Migrator;

use EasyDocLabs\Library;

class MigratorBehaviorImportInsert extends Library\ControllerBehaviorAbstract
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
            'insert_limit' => '100'
        ));

        parent::_initialize($config);
    }

    /**
     * Insert task handler.
     *
     * Insert data from files to database tables.
     *
     * @param MigratorContext $context
     *
     * @return array The task output.
     *
     * @throws \RuntimeException
     */
    protected function _actionInsert(MigratorContext $context)
    {
        $job = $context->getJob();
        $job->append($this->getConfig());
        $job->append(array(
            'format' => 'csv',
            'data' => array(
                'offset' => (int) $this->getRequest()->getData()->offset
            )
        ));

        $table  = $job->table;
        $offset = $job->data->offset;
        $source = $job->source;
        $format = $job->format;

        if ($offset == 0 && $job->create_from)
        {
            $db = $this->getObject('database');

            $query = $db->replaceTableNeedle('DROP TABLE IF EXISTS #__'.$job->table);
            $db->execute($query);

            $query = $db->replaceTableNeedle(sprintf('CREATE TABLE #__%s LIKE #__%s', $job->table, $job->create_from));
            $db->execute($query);
        }

        $limit = $job->insert_limit;

        $file = sprintf('%s/%s.%s', $job->folder, $source, $format);

        if (!file_exists($file)) {
            throw new \RuntimeException('The file to be inserted does not exists');
        }

        $file = new \SplFileObject($file);

        $method = sprintf('_insert%s', ucfirst($format));

        if (method_exists($this, $method))
        {
            $inserted = $this->$method($file, $table, $offset, $limit);

            // Rewind to count the total number of lines
            $file->rewind();

            $total     = iterator_count($file)-1;
            $last_line = $offset + $inserted;
            $remaining = $total-$last_line;

            $output = array(
                'completed' => $inserted,
                'total'     => $total,
                'remaining' => $remaining,
                'offset'    => $last_line
            );
        }
        else throw new \RuntimeException('Unknown format type: ' . $format);

        return $output;
    }

    /**
     * Inserts CSV data into a database table.
     *
     * @param \SplFileObject $file   The file containing the data.
     * @param string        $table  The table name.
     * @param int           $offset The insert offset.
     * @param int           $limit  The insert limit.
     *
     * @return int The amount of data rows that got inserted.
     */
    protected function _insertCSV(\SplFileObject $file, $table, $offset = 0, $limit = 100)
    {
        $db    = $this->getObject('database');
        $query = $this->getObject('database')->getQuery('insert');

        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl(',', '"', '"');

        $query->table($table);

        $unset = array();

        $rows_per_query = 20;
        $queue_count    = 0;
        $total_count    = 0;
        $columns        = [];

        foreach ($file as $i => $row)
        {
            if ($i === 0)
            {
                $row = str_replace("\xEF\xBB\xBF", '', $row);

                $unset = $this->_getIgnoredOffsets($row, $query->table);

                $this->_unsetOffsets($row, $unset);

                $query->columns($row);

                $schema = $this->getObject('lib:database.table.default', ['name' => $table])->getSchema();

                foreach ($row as $column)
                {
                    if (isset($schema->columns[$column])) {
                        $columns[] = $schema->columns[$column];
                    }
                }

                continue;
            }

            if ($offset && $i <= $offset) {
                continue;
            }

            $this->_unsetOffsets($row, $unset);

            $this->_convertNullDates($row);

            $this->_convertEmptyValues($row, $columns);

            $query->values($row);
            $total_count++;
            $queue_count++;

            if ($queue_count === $rows_per_query)
            {
                $db->execute(str_replace('INSERT', 'INSERT IGNORE', $query->toString()));
                $query->values = array();
                $queue_count   = 0;
            }

            if ($limit && $total_count === $limit) {
                break;
            }
        }

        // There are some rows pending insert
        if ($queue_count) {
            $db->execute(str_replace('INSERT', 'INSERT IGNORE', $query->toString()));
        }

        return $total_count;
    }

    protected function _convertEmptyValues(&$row, $columns)
    {
        $types = ['int', 'bigint', 'tinyint', 'mediumint', 'smallint', 'time', 'timestamp', 'year', 'date', 'datetime']; // A list of allowed types for the conversion

        foreach ($row as $key => $value)
        {
            if (isset($columns[$key]))
            {
                $default  = $columns[$key]->default;
                $required = $columns[$key]->required;
                $type     = $columns[$key]->type;

                if ($value === '' && $default === null && !$required && in_array($type, $types)) {
                    $row[$key] = null;
                }
            }
        }
    }

    protected function _convertNullDates(&$row) 
    {
        foreach ($row as $i => $value) {
            if ($value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
                $row[$i] = null;
            }
        }
    }

    /**
     * Returns a list of array offsets to be ignored
     *
     * The ignored offsets correspond to the offsets from a list of columns that are not present in a given table.
     *
     * @param array  $columns An array containing column names.
     * @param string $table   The table name.
     *
     * @return array An array containing the ignored offsets.
     */
    protected function _getIgnoredOffsets(array $columns, $table)
    {
        $db    = $this->getObject('database');

        $schema = $db->getTableSchema($table);

        $table_columns = array_keys($schema->columns);
        $result = array();

        foreach ($columns as $i => $column)
        {
            if (!in_array($column, $table_columns)) {
                $result[] = $i;
            }
        }

        return $result;
    }

    /**
     * Unsets a list of offsets from an array.
     *
     * @param array $array   The array to unset offsets from.
     * @param array $offsets An array of offsets to unset.
     */
    protected function _unsetOffsets(&$array, array $offsets)
    {
        foreach ($offsets as $offset)
        {
            unset($array[$offset]);
        }
    }
}