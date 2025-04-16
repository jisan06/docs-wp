<?php

require_once EASY_DOCS_PATH . 'foliokit/wordpress.php';

use EasyDocLabs\WP;

WP::add_action('wp_loaded', function()
{
    if (WP::isFlushPending()) {
        WP::flushRewriteRules();
    }
});

WP::add_action('plugins_loaded', 'easydoc_bootstrap');
WP::add_action('easydoc_after_bootstrap', 'easydoc_setup_session');
WP::add_action('easydoc_after_bootstrap', 'easydoc_setup_pages');
WP::add_action('easydoc_after_bootstrap', 'easydoc_setup_language_debug');
WP::add_action('init', 'easydoc_init');

//WP::register_uninstall_hook(__FILE__, 'easydoc_uninstall');
//
//function easydoc_uninstall()  {
//	WP::delete_option('easy_docs_ait_version');
//}

/*
 * Create an output buffer that respects other plugins
 */
WP::add_action('template_redirect', function(){
    ob_start(function ($content_of_the_buffer){
        return $content_of_the_buffer;
    });
}, PHP_INT_MAX);

WP::add_action('shutdown', function(){
    if (ob_get_level()) {
        @ob_end_flush();
    }
}, -1 * PHP_INT_MAX);

WP::add_action('switch_blog', function ()
{
    // Make sure to set the current blog prefix into the DB driver

    global $wpdb;

    // Some plugins actually fire a blog switch before all plugins are loaded (user role editor)

    if (class_exists('\Foliokit')) {
        \Foliokit::getObject('lib:database.driver.mysqli')->setTablePrefix($wpdb->prefix);
    }
}, 999, 0);

WP::add_action('wp_login', function($user_login, $user)
{
    try {
        if (!WP::is_admin() && is_a( $user, 'WP_User' ) && $user->exists())
        {
            $redirect = \Foliokit::getObject('request')->getData()->redirect_to;

            if ($redirect)
            {
                $url = \Foliokit::getObject('lib:http.url', ['url' => base64_decode($redirect)]);

                $query = $url->getQuery(true);

                if (isset($query['easydoc_login_redirect']) && $query['easydoc_login_redirect'] == 1)
                {
                    unset($query['easydoc_login_redirect']);

                    $url->setQuery($query);

                    \Foliokit::getObject('response')->setRedirect($url->toString())->send();
                }
            }
        }
    } catch (Exception $e) {
        if (\Foliokit::isDebug()) throw $e;
    }

}, 2, 10);

WP::add_action('customize_save_after', function() {
	WP::flush_rewrite_rules(); // The rewrite rules are not flushed when switching from static page to latest post on customizer
});

WP::add_filter('login_message', function($message) {
    try {
        $query = \Foliokit::getObject('request')->getQuery();

        if (isset($query['redirect_to'])) {
            $url = \Foliokit::getObject('lib:http.url', ['url' => base64_decode($query['redirect_to'])]);

            $query = $url->getQuery(true);

            if (isset($query['easydoc_login_redirect']) && $query['easydoc_login_redirect'] == 1) {
                $message = \Foliokit::getObject('translator')->translate('You are not authorized to access this resource. Please login and try again.');
            }
        }

        return $message;
    } catch (Exception $e) {
        if (\Foliokit::isDebug()) throw $e;
    }

}, 10, 3);

WP::add_filter('rewrite_rules_array', function($wp_rules)
{
	$pages = EasyDocLabs\Component\Base\BlockPage::getPages();

	foreach ($pages as $page)
	{
		if (isset($page->rewrite_rule)) {
			$wp_rules = array_merge($page->rewrite_rule, $wp_rules); // Put our rules on top
		}
	}

	return $wp_rules;
}, PHP_INT_MAX, 1); // Lowest prio possible

WP::add_action('login_head', function() {
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    try {
      const redirectToElement = document.querySelector('input[name="redirect_to"]');
      let redirectTo = redirectToElement ? redirectToElement.value : false;

      if (redirectTo && document.referrer) {
        redirectTo = new URL(atob(redirectTo));

        if (redirectTo.searchParams.get('easydoc_login_redirect')) {
            history.replaceState({}, '', document.referrer.toString());
        }
      }
    } catch (e) {}
});
</script>
<?php
});

/*
 * Clear old files on package installs on packages with a name that starts with "easydoclabs"
 *
 * This allows overriding packages through the plugin uploader in backend.
 */
WP::add_filter('upgrader_package_options', function($options) {
    $isPackageInstall = isset($options['package']) && isset($options['hook_extra'])
        && (strpos(basename($options['package']), 'easydoclabs') === 0 || strpos(basename($options['package']), 'ait_themes') === 0)
        && isset($options['hook_extra']['type']) && $options['hook_extra']['type'] === 'plugin'
        && isset($options['hook_extra']['action']) && $options['hook_extra']['action'] === 'install';

    if ($isPackageInstall) {
        $options['clear_destination'] = true;
        $options['abort_if_destination_exists'] = false;
    }

    return $options;
});


/**
 * Registers a plugin and saves its asset paths
 *
 * @param $plugin_file string Absolute file path for the plugin file
 */
function easydoc_register_plugin($plugin_file)
{
    $manager = \Foliokit::getObject('manager');

    $directory    = dirname($plugin_file);
    $bootstrapper = $manager->getObject('object.bootstrapper');

    $package = str_replace('easydoclabs-', '', basename($plugin_file,'.php'));

    $has_base  = is_dir($directory . '/base');
    $has_site  = is_dir($directory . '/site');
    $has_admin = is_dir($directory . '/admin');

    if ($has_base) {
        $bootstrapper->registerComponent($directory.'/base', true);
    }

    if ($has_admin) {
        $bootstrapper->registerComponent($directory.'/admin', WP::is_admin());
    }

    if ($has_site) {
        $bootstrapper->registerComponent($directory.'/site', !WP::is_admin());
    }

    $bootstrapper->registerExtension('ext:' . $package); // Register the extension folder
}

/**
 * Bootstrap Framework
 */
function easydoc_bootstrap()
{
    try
    {
        $path = EASY_DOCS_PATH . 'foliokit/library/foliokit.php';
        if (file_exists($path))
        {

            /**
             * Foliokit Bootstrapping
             *
             * If FOLIOKIT is defined assume it was already loaded and bootstrapped
             */
            if (!defined('FOLIOKIT'))
            {

                require_once $path;

                $application = WP::is_admin() ? 'admin' : 'site';

                /**
                 * Framework Bootstrapping
                 */
                \Foliokit::getInstance(array(
                    'debug'           => WP::isFolioDebug() ?: null,
                    'cache'           => false,
                    'cache_namespace' => 'easydoc-' . $application . '-' . md5(WP\NONCE_KEY),
                    'root_path'       => WP\ABSPATH,
                    'base_path'       => WP\ABSPATH,
                ));

                /**
                 * Plugin Bootstrapping
                 *
                 * Only bootstrap the framework plugin, other plugins need to register themselves with the bootstrapper
                 * using the easydoc_before_bootstrap hook.
                 */

                $bootstrapper = \Foliokit::getObject('object.bootstrapper')
                    ->registerComponents(__DIR__.'/component');

                // Load and bootstrap custom vendor directory if it exists
                $custom_vendor = __DIR__.'/vendor';
                if (is_dir($custom_vendor) && file_exists($custom_vendor.'/autoload.php')) {
                    require_once $custom_vendor.'/autoload.php';
                }

                WP::do_action('easydoc_before_bootstrap');

                $bootstrapper->bootstrap();

                WP::do_action('easydoc_after_bootstrap');

                $url = \Foliokit::getObject('lib:http.url', ['url' => rtrim(WP::is_admin() ? WP::get_site_url(null, '/wp-admin') : WP::get_home_url(), '/\\')]);

                $request = \Foliokit::getObject('request');

                $request->setBasePath(rtrim($url->getPath(), '/\\'));
                $request->setBaseUrl($url);

                /**
                 * Output Buffering
                 *
                 * Wordpress doesn't implement output buffer. Setup a buffer to gain control over output rendering.
                 */
                ob_start();
            }
        }
    }
    catch (\Exception $e) {
        if (\Foliokit::isDebug()) throw $e;
    }

}

/**
 * Sets up page handler by initializing the page controller
 */
function easydoc_setup_pages()
{
    try {
        \Foliokit::getObject('com:base.dispatcher.page');
    } catch (Exception $e) {
        if (\Foliokit::isDebug()) throw $e;
    }
}

/**
 * Starts the PHP session and destroys session on login and logout
 * @throws Exception
 */
function easydoc_setup_session()
{
    try {
        \Foliokit::getObject('user.session')->start();
    } catch (Exception $e) {}
}


/**
 * Adds the shutdown handler for untranslated keys if language debug is enabled
 *
 * To enable language debug, create an empty file at wp-content/easydoclabs/debug-lang
 * To see untranslated strings, check wp-content/easydoclabs/untranslated-strings.json after enabling debug
 * @throws Exception
 */
function easydoc_setup_language_debug()
{
    try {
        if (WP::isLanguageDebug()) {
            \Foliokit::getObject('translator')->setDebug(true);

            register_shutdown_function(function () {
                $strings    = \Foliokit::getObject('translator')->getUntranslatedStrings();
                $file       = WP\CONTENT_DIR.'/easydoclabs/untranslated-strings.json';
                $existing   = [];

                if (file_exists($file)) {
                    $existing = @json_decode(@file_get_contents($file), true);

                    if (!$existing) {
                        $existing = [];
                    }
                }

                $existing = array_merge($existing, $strings);

                ksort($existing);

                @file_put_contents($file, json_encode($existing, JSON_PRETTY_PRINT));
            });
        }
    } catch (Exception $e) {}
}

/**
 * Dispatch Component
 */
function easydoc_init()
{
    if (WP::did_action('easydoc_after_bootstrap')) {
        try {
            \Foliokit::getObject('com:base.dispatcher.page')->init();
        } catch (\Exception $e) {
            if (\Foliokit::isDebug()) throw $e;
        }

    }
}
