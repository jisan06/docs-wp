<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Files;

use EasyDocLabs\Library;

/**
 * Directory Iterator
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class IteratorDirectory extends \DirectoryIterator
{
    /**
     * Method to get files in a folder
     *
     * @param array $config
     * @return array
     */
    public static function getFiles($config = [])
    {
        $config['type'] = 'files';
        return self::getNodes($config);
    }

    /**
     * Method to get child folders of a folder
     *
     * @param array $config
     * @return array
     */
    public static function getFolders($config = [])
    {
        $config['type'] = 'folders';
        return self::getNodes($config);
    }

    /**
     * Returns the total number of nodes in the given path
     *
     * @param array $config
     * @return integer Total count
     */
    public static function countNodes($config = [])
    {
        $config = new Library\ObjectConfig($config);
        $config->append([
            'path' 		=> null, // path to the directory
            'fullpath' 	=> false, // true to return full paths, false to return basename only
            'exclude' 	=> ['.svn', '.git', 'CVS'], // an array of values to exclude from results
        ]);

        $exclude = Library\ObjectConfig::unbox($config->exclude);
        $count   = 0;

        if ($handle = opendir($config->path))
        {
            while (false !== ($entry = readdir($handle)))
            {
                if ($entry === '.' && $entry === '..' || substr($entry, 0, 2) === '._' || in_array($entry, $exclude)) {
                    continue;
                }
                $count++;
            }

            closedir($handle);
        }

        return $count;
    }

    /**
     * Method to read child nodes of a folder
     *
     * @param array $config
     * @return array
     */
    public static function getNodes($config = [])
    {
        $config = new Library\ObjectConfig($config);
        $config->append([
            'path' 		=> null, // path to the directory
            'type' 		=> null, // folders or files, null for both
            'recurse' 	=> false, // boolean or integer to specify the depth
            'fullpath' 	=> false, // true to return full paths, false to return basename only
            'filter' 	=> null, // either an array of file extensions, a regular expression or a callback function
            'map' 		=> null, // a callback to return values from items in the iterator
            'exclude' 	=> ['.svn', '.git', 'CVS'], // an array of values to exclude from results
            'sort' 		=> 'name',
            'return_raw'=> false,
            'count'     => false, // only returns a node count if true
        ]);

        $exclude = Library\ObjectConfig::unbox($config->exclude);
        $filter  = Library\ObjectConfig::unbox($config->filter);
        $map     = Library\ObjectConfig::unbox($config->map);
        $sort    = $config->sort ? $config->sort : 'name';
        $recurse = $config->recurse;

        $results = [];
        $total   = 0;

        if (!is_dir($config->path)) {
            return false;
        }

        foreach (new self($config->path) as $file)
        {
            if ($file->isDot() || substr($file->getFilename(), 0, 2) === '._' || in_array($file->getFilename(), $exclude)) {
                continue;
            }

            if ($file->isDir() && !$file->isDot() && $recurse)
            {
                $clone = clone $config;
                $clone->path = $file->getPathname();
                $clone->recurse = is_int($config->recurse) ? $config->recurse - 1 : $config->recurse;
                $clone->return_raw = true;
                $child_results = self::getNodes($clone);
            }

            if ($config->type)
            {
                $method = 'is'.ucfirst($config->type === 'files' ? 'file' : 'dir');
                if (!$file->$method()) {
                    continue;
                }
            }

            if ($filter)
            {
                $ignore = null;

                if (is_callable($filter)) {
                    $ignore = call_user_func($filter, $file->getPathname()) === false;
                } else if (is_array($filter)) {
                    $ignore = !in_array(strtolower($file->getExtension()), $filter);
                } else if (is_string($filter)) {
                    $ignore = !preg_match("/$filter/", $file->getFilename());
                }

                if ($ignore) {
                    continue;
                }
            }

            if ($config->count)
            {
                $total++;

                if (!empty($child_results)) {
                    $total += $child_results;
                }
            }
            else
            {
                if (is_callable($map)) {
                    $result = call_user_func($map, $file->getPathname());
                } else {
                    $result = $config->fullpath ? $file->getPathname() : $file->getFilename();
                }

                $results[] = ['path' => $result, 'modified' => $file->getMTime()];

                if (!empty($child_results)) {
                    $results = array_merge($results, $child_results);
                }
            }
        }

        if ($config->count) {
            return $total;
        }

        if ($sort === 'modified_on') {
            uasort($results, [self::class, '_sortByDate']);
        }
        elseif ($sort === 'name') {
            uasort($results, [self::class, '_sortByName']);
        }

        if ($config->return_raw === true) {
            return $results;
        }

        $return = [];
        foreach ($results as $result) {
            $return[] = $result['path'];
        }

        return $return;
    }

    public function getExtension(): string
    {
        $filename  = $this->getFilename();
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        return strtolower($extension);
    }

    public static function _sortByDate($file1, $file2)
    {
        return strcmp($file1['modified'], $file2['modified']);
    }

    public static function _sortByName($file1, $file2)
    {
        return strcmp(strtolower($file1['path']), strtolower($file2['path']));
    }
}
