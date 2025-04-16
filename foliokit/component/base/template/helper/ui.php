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
 * Date Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class TemplateHelperUi extends Library\TemplateHelperUi
{
    /**
     * Loads the common UI libraries
     *
     * @param array $config
     * @return string
     */
    public function load($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'debug' => \Foliokit::isDebug(),
            'wrapper_class' => [
                WP::is_rtl() ? 'k-ui-rtl' : 'k-ui-ltr'
            ]
        ]);

        if($config->decorator === 'foliokit')
        {
            if (!WP::is_admin() && $config->layout === 'form') {
                $config->domain = 'admin';
            }
        }

        $html = parent::load($config);

        return $html;
    }

    /**
     * Returns formatted date according to current local
     *
     * @param  array  $config An optional array with configuration options.
     * @return string Formatted date.
     */
    public function styles($config = [])
    {
        $identifier = $this->getIdentifier();

        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'debug' => \Foliokit::isDebug(),
            'package' => $identifier->package ?: $config->folder,
            'domain'  => $identifier->domain ?: $config->file,
            'color-scheme' => 'no-preference', // ['light', 'dark', 'no-preference']
            'dark_mode' => true
        ])->append([
            'folder' => $config->package,
            'file'   => $config->domain,
        ]);

        $folder   = $config->folder;
        $file     = $config->file;
        $path     = 'base/css';
        $siblings = [$file];

        $component_paths = $this->getObject('object.bootstrapper')->getComponentPaths($config->package, $config->domain);

        if (count($component_paths))
        {
            // Remove foliokit base component from paths
            array_pop($component_paths);

            foreach ($component_paths as $asset_folder)
            {
                $file_path = sprintf('%s/resources/assets/css/%s.css', $asset_folder, $file);

                if (file_exists($file_path))
                {
                    $siblings = array_map(function($path) { return \Foliokit\pathinfo($path, PATHINFO_FILENAME); }, glob(str_replace('.css', '*.css', $file_path)));

                    if (basename($asset_folder) === 'base') {
                        $path = sprintf('%s/css', $folder);
                    } else {
                        $path = sprintf('%s/%s/css', $folder, $config->domain);
                    }

                    break;
                }
            }
        }

        $fileDark    = $file.'-dark';
        $colorScheme = $config->{'color-scheme'};
        $loadSingle  = $colorScheme === 'light' || $colorScheme === 'dark' || !in_array($fileDark, $siblings);
        $getFileName = function ($file, $siblings) use($config) {
            return !$config->debug && in_array($file.'.min', $siblings) ? $file.'.min' : $file;
        };

        $html = '';

        if ($loadSingle) {
            $fileToLoad = $getFileName($colorScheme === 'dark' ? $fileDark : $file, $siblings);
            $html .= '<ktml:style src="assets://'.$path.'/'.$fileToLoad.'.css" />';
        }
        else {
            $lightFile = $getFileName($file, $siblings);
            $darkFile  = $getFileName($fileDark, $siblings);

            $html .= '<ktml:style src="assets://'.$path.'/'.$darkFile.'.css" media="(prefers-color-scheme: dark)" />';
            $html .= '<ktml:style src="assets://'.$path.'/'.$lightFile.'.css" media="(prefers-color-scheme: no-preference), (prefers-color-scheme: light)" />';
        }

        if ($config->decorator === 'wordpress')
        {
            // Load Bootstrap file in site
            if ($config->domain === 'site' && !is_admin()) {
                $html .= $this->createHelper('behavior')->bootstrap(['javascript' => false, 'css' => true]);
            }
        }

        return $html;
    }
}
