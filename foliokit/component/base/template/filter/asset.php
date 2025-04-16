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
 * Url Template Filter
 *
 * Filter allows to create url schemes that are replaced on compile and render.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class TemplateFilterAsset extends Library\TemplateFilterAsset
{
    /**
     * Static cache of component asset paths
     *
     * @var array
     */
    protected static $_component_assets = [];

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   Library\ObjectConfig $config Configuration options
     * @return  void
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $content_url = WP::content_url();

        if (empty(static::$_component_assets))
        {
            /**
             * This is a map of plugins in the WP folder to their real path (which can be symlinks)
             */
            $wp_plugin_paths = WP::global('wp_plugin_paths');

            $fallback     = [];
            $specific     = [];
            $bootstrapper = $this->getObject('object.bootstrapper');

            foreach($bootstrapper->getComponents() as $component)
            {
                $identifier = $this->getIdentifier($component);
                $package    = $identifier->getPackage();
                $domain     = $identifier->getDomain();

                if ($paths = $bootstrapper->getComponentPaths($package, $domain))
                {
                    $path        = realpath($paths[0]); // We don't care about override paths
                    $plugin_path = array_search($path, $wp_plugin_paths);
                    $plugin_name = basename($plugin_path);

                    if (strpos($path, 'easydoclabs')) {
                        // Custom overrides
                        $fallback['assets://'.$domain.'/'.$package] = WP\CONTENT_URL.'/'.$domain.'/'.$plugin_name.'/resources/assets';
                    }
                    else if (strpos($path, sprintf('foliokit%1$scomponent%1$s', DIRECTORY_SEPARATOR)) !== false) {
                        // Reusable component
                        $fallback['assets://'.$package] = EASY_DOCS_URL.'/foliokit/component/'.$package.'/resources/assets';
                    }
                    else if ($plugin_path) // Plugin at the root, no site/ or admin/ folders
                    {
                        $fallback['assets://'.$package] = EASY_DOCS_URL.'/resources/assets';
                    }
                    else // Add site/ or admin/ folders along with base/
                    {
                        $plugin_path = rtrim(dirname($path), '/');

                        $isStandardPlugin = (is_dir($plugin_path) && strpos($plugin_path, str_replace("/", DIRECTORY_SEPARATOR, WP\PLUGIN_DIR)) === 0);

                        if ($isStandardPlugin || ($plugin_path = array_search($plugin_path, $wp_plugin_paths)))
                        {
                            $plugin_name = basename($plugin_path);

                            if ($domain) {
                                $specific['assets://'.$package.'/'.$domain] = EASY_DOCS_URL.'/'.$domain.'/resources/assets';
                            }

                            $fallback['assets://'.$package] = EASY_DOCS_URL.'/base/resources/assets';
                        }
                        elseif ($domain == 'custom')
                        {
                            $fallback['assets://custom/'.$package] = $content_url.'/easydoclabs/custom/'.$package.'/resources/assets';
                        }
                        else $fallback['assets://'.$package] = EASY_DOCS_URL.'/foliokit/component/'.$package.'/resources/assets'; // Reusable component

                    }
                }
            }

            static::$_component_assets = array_merge($specific, $fallback);
        }

        $config->append([
            'schemes' => static::$_component_assets
        ])->append([
            'schemes' => [
                'root://'        => rtrim($this->getObject('request')->getSiteUrl()->getPath(), '/').'/',
                'base://'        => rtrim($this->getObject('request')->getBaseUrl()->getPath(), '/').'/',
                'assets://base/' => EASY_DOCS_URL.'/foliokit/component/base/resources/assets/',
                'assets://'      => EASY_DOCS_URL.'/foliokit/library/resources/assets/',
            ]
        ]);

        parent::_initialize($config);
    }
}