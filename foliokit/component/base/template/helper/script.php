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
 * Script Template Helper
 * 
 * Serves as an interface for loading WordPress core scripts
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Base
 */
class TemplateHelperScript extends Library\TemplateHelperAbstract
{
    protected $_map = [
        'jquery' => 'jquery-core',
        'jquery-ui' => 'jquery-ui-core',
        'plupload' => 'plupload-all'
    ];

    static protected $_loaded = [];


    /**
     * Script source getter 
     *
     * @param array $config An optional configuration array
     * @return mixed A single or an array of sources (if dependencies are enabled), false if a source cannot be found
     */
    public function src($config = [])
    {
        $config = new Library\ObjectConfig($config);

        $config->append(['map' => true, 'dependencies' => true]);

        $name = $config->name;

        if (!$name) throw new \RuntimeException('Script name is missing');

        if ($config->map) {
            $name = $this->map($name);
        }

        $wp_scripts = WP::wp_scripts();
    
        ob_start();

        if ($config->dependencies) {
            $wp_scripts->do_items($name);
        } else {
            $wp_scripts->do_item($name);
        }

        $tags = ob_get_clean();

        $src = false;

        if ($tags)
        {
            if ($config->dependencies) {
                preg_match_all('/src="(.*?)"/', $tags, $result);
            } else {
                preg_match('/src="(.*?)"/', $tags, $result);
            }       

            if ($result) $src = $result[1];
        }

        return $src;
    }
    
    /**
     * Maps a script name to its WordPress named counterpart
     * 
     * @return string The mapped script
     */
    public function map($script)
    {
        return $this->_map[$script] ?? $script;
    }

    /**
     * Dependencies getter
     *
     * @param string $name The name of script to get dependecies for
     * @return array An array of dependencies names
     */
    static public function dependencies($name)
    {
        if (!$name) throw new \RuntimeException('Script name is missing');

        $wp_scripts = WP::wp_scripts();

        $tmp = $wp_scripts->to_do;

        $wp_scripts->to_do = []; // Reset

        $wp_scripts->all_deps($name);

        $dependencies = $wp_scripts->to_do;

        $wp_scripts->to_do = $tmp; // Put it all back

        return $dependencies;
    }

    /**
     * KTML script tag getter
     *
     * @param array $config An optional configuration array
     * @return string The tag(s), empty string if no source is found for the provided script(s)
     */
    public function tag($config = [])
    {
        $config = new Library\ObjectConfig($config);

        $config->append(['map' => true]);

        $scripts = (array) $config->name;

        if (empty($scripts)) throw new \RuntimeException('Script name(s) missing');

        $html = '';

        foreach ($scripts as $script)
        {
            $config->name = $script;

            if ($result = $this->src($config))
            {
                $sources = (array) $result;

                foreach ($sources as $source) {
                    $html .= $this->buildElement('ktml:script', ['src' => $source]);
                }
            }
        }

        return $html;
    }

    /**
     * Script(s) loader
     *
     * @param array $config An optional configuration array
     * @return string KTML tags if decorator is not WordPress (forms|decorator=foliokit) and sources are found, empty string otherwise
     */
    public function load($config = [])
    {
        $config = new Library\ObjectConfig($config);

        $config->append(['enqueue' => ($config->decorator ?? 'wordpress') == 'wordpress' ? true : false]);

        $scripts = (array) $config->name;

        if (empty($scripts)) throw new \RuntimeException('Script name(s) missing');

        $html = '';

        foreach ($scripts as $script)
        {
            // Check if there's an alias for the script

            $script = $this->map($script);

            if (!static::isLoaded($script))
            {
                if ($config->enqueue) {
                    WP::wp_enqueue_script($script);
                } else {
                    $html .= $this->tag(['map' => false, 'name' => $script]);
                }

                self::setLoaded($script);
            }
        }

        return $html;
    }


    /**
     * Marks the script as loaded
     *
     * @param String $name The name of the script
     */
    public static function setLoaded($name)
    {
        $scripts = self::dependencies($name);

        $scripts[] = $name;

        foreach ($scripts as $script) {
            static::$_loaded[$script] = true;
        }
    }

    /**
     * Checks if the script is loaded
     *
     * @param String $name The name of the string
     * @return bool True if loaded, false otherwise
     */
    public static function isLoaded($name)
    {
        return !empty(static::$_loaded[$name]);
    }
}
