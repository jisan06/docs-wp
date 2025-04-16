<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs {

    /**
     * Wordpress functions
     *
     * @author  Ercan Özkaya <https://github.com/ercanozkaya>
     * @package EasyDocLabs\Wordpress
     *
     */
    class WP {

        static protected $_enqueued_scripts = [];

        static protected $_enqueued_styles = [];

        public static function wp_enqueue_script($handle, $src = '', $deps = [], $ver = false, $in_footer = false)
        {
            if (!in_array($handle, self::getEnqueuedScripts())) {
                self::$_enqueued_scripts[] = $handle;
            }

            wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
        }

        public static function wp_enqueue_style($handle, $src = '', $deps = [], $ver = false, $media = 'all')
        {
            if (!in_array($handle,  self::getEnqueuedStyles())) {
                self::$_enqueued_styles[] = $handle;
            }

            wp_enqueue_style($handle, $src, $deps, $ver, $media);
        }

        public static function getEnqueuedScripts()
        {
            return self::$_enqueued_scripts;
        }

        public static function getEnqueuedStyles()
        {
            return self::$_enqueued_styles;
        }

        public static function isWordpressDebug() {
            return (defined('WP_DEBUG') && WP_DEBUG);
        }

        public static function isFolioDebug() {
            return (getenv('FOLIOKIT_DEBUG') !== false ? filter_var( getenv('FOLIOKIT_DEBUG') , FILTER_VALIDATE_BOOLEAN) : false) || file_exists(static::_getDebugFilePath());
        }

        public static function isFlushPending()
        {
            return self::get_option('easydoc_flush_rewrite_rules');
        }

        public static function flushRewriteRules($defer = false)
        {
            if (!$defer)
            {
                self::flush_rewrite_rules();

                self::delete_option('easydoc_flush_rewrite_rules');
            }
            else self::add_option('easydoc_flush_rewrite_rules', true);
        }

        public static function isDebug() {
            return static::isWordpressDebug() || static::isFolioDebug();
        }

        public static function setDebug($state)
        {
            if (!$state)
            {
                if (\file_exists(static::_getDebugFilePath())) {
                    \unlink(static::_getDebugFilePath());
                }
            }
            else \touch(static::_getDebugFilePath());
        }

        protected static function _getDebugFilePath() {
            return WP\CONTENT_DIR.'/easydoclabs/debug';
        }

        public static function getPluginUrl()
        {
            $url = \Foliokit::getObject('lib:http.url', ['url' => WP\PLUGIN_URL]);

            if ($url->getHost()) {
                $url->setScheme(\Foliokit::getObject('request')->getScheme());
            }

            return $url->toString();
        }

        public static function isLanguageDebug() {
            return file_exists(static::_getLanguageDebugFilePath());
        }

        public static function setLanguageDebug($state)
        {
            if (!$state)
            {
                if (\file_exists(static::_getLanguageDebugFilePath())) {
                    \unlink(static::_getLanguageDebugFilePath());
                }
            }
            else \touch(static::_getLanguageDebugFilePath());
        }

        protected static function _getLanguageDebugFilePath() {
            return WP\CONTENT_DIR.'/easydoclabs/debug-lang';
        }

        /**
         * @param string $name Name of the global variable e.g. wp_query
         * @return mixed
         */
        public static function global($name) {
            if (!isset($GLOBALS[$name])) {
                throw new \UnexpectedValueException(sprintf('Cannot find a global variable named $%s', $name));
            }

            return $GLOBALS[$name];
        }

        /**
         * WP Filesystem instance getter
         *
         * @return WP_Filesystem_* 
         */
        public static function getFilesystem()
        {
            global $wp_filesystem;

            // Make sure that the above variable is properly setup.
            require_once ABSPATH . 'wp-admin/includes/file.php';

            self::WP_Filesystem();

            return $wp_filesystem;
        }

        /**
         * Proxy static method calls to the Wordpress function
         *
         * @param  string     $method    The function name
         * @param  array      $arguments The function arguments
         * @return mixed 
         */
        public static function __callStatic($method, $arguments)
        {
            return call_user_func_array($method, $arguments);
        }
    }
}

namespace EasyDocLabs\WP {
    define(__NAMESPACE__.'\ABSPATH', ABSPATH);
    define(__NAMESPACE__.'\CONTENT_DIR', WP_CONTENT_DIR);
    define(__NAMESPACE__.'\CONTENT_URL', WP_CONTENT_URL);
    define(__NAMESPACE__.'\DAY_IN_SECONDS', DAY_IN_SECONDS);
    define(__NAMESPACE__.'\DEBUG', WP_DEBUG);
    define(__NAMESPACE__.'\DEBUG_DISPLAY', defined('WP_DEBUG_DISPLAY') ? WP_DEBUG_DISPLAY : true);
    define(__NAMESPACE__.'\AUTH_KEY', AUTH_KEY);
    define(__NAMESPACE__.'\NONCE_KEY', NONCE_KEY);
    define(__NAMESPACE__.'\PLUGIN_DIR', WP_PLUGIN_DIR);
    define(__NAMESPACE__.'\PLUGIN_URL', WP_PLUGIN_URL);
}

namespace  {
    require_once ABSPATH . 'wp-admin/includes/user.php';

    class_alias('\WP_Query', '\EasyDocLabs\WP\Query');
    class_alias('\WP_Error', '\EasyDocLabs\WP\Error');
    class_alias('\WP_Post', '\EasyDocLabs\WP\Post');

    if (class_exists('\WP_Block_Parser')) {
        class_alias('\WP_Block_Parser', '\EasyDocLabs\WP\Block_Parser');
    }

}