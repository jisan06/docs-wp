<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

/*
 * Hello there, welcome to the horrible horrible installation code of Foliokit.
 * Let me try to answer the questions you have in mind.
 *
 * - Why are there no named classes or functions here?
 * + Because the class would be included again on install while the old one with the same name
 * would be already included by PHP. So your new installer code wouldn't be run.
 *
 * - Do we really have to do this?
 * + Anonymous classes are PHP 7.0+ only, so yeah, I think we do.
 *
 * - Does it actually work well?
 * + Heck yeah, it does.
 */
require_once ABSPATH . 'wp-admin/includes/plugin.php';

global $wpdb;

$getNewVersion = function($plugin_file)
{
    $metadata    = get_plugin_data($plugin_file, false, false);
    $new_version = null;

    if ($metadata && isset($metadata['Version'])) {
        $new_version = $metadata['Version'];
    }

    return $new_version;
};

$deleteFolder = function($src) use(&$deleteFolder) {
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            $full = $src . '/' . $file;
            if ( is_dir($full) ) {
                $deleteFolder($full);
            }
            else {
                unlink($full);
            }
        }
    }
    closedir($dir);
    rmdir($src);

    return true;
};

$moveFolder = function($src, $dest)
{
    if (!is_dir($src) || is_dir($dest)) {
        return false;
    }

    return @rename($src, $dest);
};

$copyFolder = function($src, $dest) use(&$copyFolder)
{
    // Eliminate trailing directory separators, if any
    $src = rtrim($src, DIRECTORY_SEPARATOR);
    $dest = rtrim($dest, DIRECTORY_SEPARATOR);

    if (!is_dir($src)) {
        return false;
    }

    if (is_dir($dest)) {
        return false;
    }

    if (!mkdir($dest)) {
        return false;
    }

    if (!($dh = @opendir($src))) {
        return false;
    }

    // Walk through the directory copying files and recursing into folders.
    while (($file = readdir($dh)) !== false)
    {
        $sfid = $src . '/' . $file;
        $dfid = $dest . '/' . $file;

        switch (filetype($sfid))
        {
            case 'dir':
                if ($file != '.' && $file != '..')
                {
                    $ret = $copyFolder($sfid, $dfid);

                    if ($ret !== true) {
                        return $ret;
                    }
                }
                break;

            case 'file':
                if (!@copy($sfid, $dfid)) {
                    return false;
                }
                break;
        }
    }

    return true;
};

$moveFolderWithBackup = function($from, $to) use ($deleteFolder, $copyFolder){
    $from = rtrim($from, '/');
    $to = rtrim($to, '/');

    $temp   = $to.'_tmp';
    $bkp    = $to.'_bkp';

    if (is_dir($temp)) {
        if (!$deleteFolder($temp) || is_dir($temp)) {
            return false;
        }
    }

    if ($copyFolder($from, $temp))
    {
        if (is_dir($to)) {
            if (!$copyFolder($to, $bkp)) {
                return false;
            }

            if (!$deleteFolder($to) || is_dir($to)) {
                return false;
            }
        }

        $result = @rename($temp, $to);

        if ($result) {
            if (is_dir($from)) {
                $deleteFolder($from);
            }

            if (is_dir($bkp)) {
                $deleteFolder($bkp);
            }

        }
    }

    return true;
};

$deleteOldFiles = function($nodes = []) use($deleteFolder)
{
    foreach ($nodes as $node)
    {
        $path = ABSPATH.'/'.$node;

        if (file_exists($path)) {
            if (is_dir($path)) {
                $deleteFolder($path);
            } else {
                unlink($path);
            }
        }
    }

    return true;
};

$replacePrefix = function($sql, $prefix = '#__') use($wpdb)
{
    $startPos  = 0;
    $literal   = '';
    $sql = trim($sql);
    $n   = \strlen($sql);
    while ($startPos < $n)
    {
        $ip = strpos($sql, $prefix, $startPos);
        if ($ip === false)
        {
            break;
        }
        $j = strpos($sql, "'", $startPos);
        $k = strpos($sql, '"', $startPos);
        if (($k !== false) && (($k < $j) || ($j === false)))
        {
            $quoteChar = '"';
            $j         = $k;
        }
        else
        {
            $quoteChar = "'";
        }
        if ($j === false)
        {
            $j = $n;
        }
        $literal .= str_replace($prefix, $wpdb->prefix, substr($sql, $startPos, $j - $startPos));
        $startPos = $j;
        $j = $startPos + 1;
        if ($j >= $n)
        {
            break;
        }
        // Quote comes first, find end of quote
        while (true)
        {
            $k       = strpos($sql, $quoteChar, $j);
            $escaped = false;
            if ($k === false)
            {
                break;
            }
            $l = $k - 1;
            while ($l >= 0 && $sql[$l] === '\\')
            {
                $l--;
                $escaped = !$escaped;
            }
            if ($escaped)
            {
                $j = $k + 1;
                continue;
            }
            break;
        }
        if ($k === false)
        {
            // Error in the query - no end quote; ignore it
            break;
        }
        $literal .= substr($sql, $startPos, $k - $startPos + 1);
        $startPos = $k + 1;
    }
    if ($startPos < $n)
    {
        $literal .= substr($sql, $startPos, $n - $startPos);
    }
    return $literal;
};

$splitSql = function($sql)
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
};

$executeQuery = function($query) use($wpdb, $replacePrefix) {
    return $wpdb->query($replacePrefix($query));
};

$executeQueries = function($queries) use($splitSql, $executeQuery)
{
    if (is_string($queries)) {
        $queries = $splitSql($queries);
    }

    foreach ($queries as $query) {
        $executeQuery($query);
    }
};

$executeSqlFile = function($file) use($executeQueries)
{
    $buffer = file_get_contents($file);

    if ($buffer !== false) {
        $executeQueries($buffer);
    }
};


$tableExists = function($table) use($wpdb)
{
    if (substr($table, 0,  3) !== '#__') {
        $table = '#__'.$table;
    }

    $table = str_replace('#__', $wpdb->prefix, $table);

    return (bool) $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
};

$columnExists = function($table, $column) use($wpdb, $tableExists)
{
    $result = false;

    if (substr($table, 0,  3) !== '#__') {
        $table = '#__'.$table;
    }

    $table = str_replace('#__', $wpdb->prefix, $table);

    if ($tableExists($table))
    {
        $query  = 'SHOW COLUMNS FROM '.$table.' WHERE Field = %s';
        $result = (bool) $wpdb->get_var($wpdb->prepare($query, $column));
    }

    return $result;
};

$indexExists = function($table, $index_name) use($wpdb, $tableExists)
{
    $result = false;

    if (substr($table, 0,  3) !== '#__') {
        $table = '#__'.$table;
    }

    $table = str_replace('#__', $wpdb->prefix, $table);

    if ($tableExists($table))
    {
        $query  = 'SHOW KEYS FROM '.$table.' WHERE Key_name = %s';
        $result = (bool) $wpdb->get_var($wpdb->prepare($query, $index_name));
    }

    return $result;
};

$backupTable = function($table) use($wpdb, $tableExists)
{
    if ($tableExists($table))
    {
        $destination = $table.'_bkp';

        if ($tableExists($destination))
        {
            $i = 2;

            while (true)
            {
                if (!$tableExists($destination.$i)) {
                    break;
                }

                $i++;
            }

            $destination .= $i;
        }

        if (substr($table, 0,  3) !== '#__') {
            $table = '#__'.$table;
        }

        if (substr($destination, 0,  3) !== '#__') {
            $destination = '#__'.$destination;
        }

        $table       = str_replace('#__', $wpdb->prefix, $table);
        $destination = str_replace('#__', $wpdb->prefix, $destination);

        $return = $wpdb->query(sprintf('RENAME TABLE `%1$s` TO `%2$s`;', $table, $destination));
    }
    else $return = true;

    return $return;
};

$clearCache = function()
{
    // Clear APC opcode cache
    if ( extension_loaded('apcu') && apcu_enabled()) {
        apcu_clear_cache();
    }

    // Clear OPcache
    if (function_exists('opcache_reset')) {
        @opcache_reset();
    }
};

$runFrameworkQueries = function($plugin_dir) use($splitSql, $executeQuery)
{
    $results = glob($plugin_dir.'/component/*/resources/install/install.sql');
    $queries = [];

    foreach ($results as $result) {
        if ($q = $splitSql(file_get_contents($result))) {
            $queries = array_merge($queries, $q);
        }
    }

    foreach ($queries as $query) {
        $query = trim($query);

        if ($query != '' && $query[0] != '#') {
            try {
                $executeQuery($query);
            } catch (\Exception $e) {
            }
        }
    }
};


//return function() use($getNewVersion, $deleteFolder, $moveFolderWithBackup, $runFrameworkQueries)
//{
//    // find root
//    $framework_dir = dirname(dirname(dirname(dirname(__DIR__))));
//
//    if (!is_file($framework_dir.'/foliokit.php')) {
//        return false;
//    }
//
//    $easydocs_files = WP_CONTENT_DIR . '/easydoclabs';
//    $easydocs_logs  = $easydocs_files . '/logs';
//    $easydocs_functions  = $easydocs_files . '/functions.php';
//    $version_option  = 'easy_docs_ait_version';
//    $plugin          = 'foliokit/foliokit.php';
//    $plugin_file     = WP_PLUGIN_DIR . '/' . $plugin;
//    $plugin_dir      = plugin_dir_path($plugin_file);
//
//    $plugin_file_contents = file_get_contents($framework_dir.'/foliokit.php');
//
//    /*
//     * We don't want Wordpress to find foliokit.php as the main extension to install
//     * We either rot13 encode foliokit.php or replace the plugin name with a placeholder
//     */
//    if (strpos($plugin_file_contents, 'Cyhtva') !== false) { // Cyhtva = str_rot13('Plugin')
//        file_put_contents($framework_dir.'/foliokit.php', str_rot13($plugin_file_contents));
//    } else if (strpos($plugin_file_contents, 'FoliokitNamePlaceholder') !== false) {
//        file_put_contents($framework_dir.'/foliokit.php', str_replace('FoliokitNamePlaceholder', 'Plugin Name', $plugin_file_contents));
//    }
//
//    $new_version = $getNewVersion($framework_dir.'/foliokit.php');
//
//    if ($framework_dir !== realpath($plugin_dir)) {
//        // check if a newer framework is installed
//        if ($installed_version = get_option($version_option)) {
//            if ($new_version && version_compare($installed_version, $new_version, '>')) {
//                $deleteFolder($framework_dir);
//
//                return;
//            }
//        }
//
//        if (!$moveFolderWithBackup($framework_dir, $plugin_dir)) {
//            $deleteFolder($framework_dir);
//
//            return;
//        }
//    }
//
//    if (!is_dir($easydocs_files)) {
//        mkdir($easydocs_files);
//    }
//
//    if (!is_dir($easydocs_logs)) {
//        mkdir($easydocs_logs);
//        file_put_contents($easydocs_logs.'/.htaccess', "DENY FROM ALL");
//    }
//
//    if (!is_file($easydocs_functions)) {
//        file_put_contents($easydocs_functions, "<?php\n// Add your custom hooks here\n\n");
//    }
//
//    $runFrameworkQueries($plugin_dir);
//
//    wp_cache_set( 'plugins', [], 'plugins');
//
//    if( is_plugin_inactive($plugin)){
//        activate_plugin($plugin);
//    }
//
//    update_option($version_option, $new_version);
//};