<?php
/*
Plugin Name: Easy Docs
Plugin URI: https://wpclient.com/document/
Description: Document Management Plugin for Wordpress
Version: 1.0.0
Author: AitThemes
Author URI: https://ait-themes.club/
Requires at least: 6.3
Requires PHP: 7.3
License: GNUGPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

defined( 'ABSPATH' ) or die();

define( 'EASY_DOCS_PATH', plugin_dir_path( __FILE__ ) );
define( 'EASY_DOCS_URL', plugin_dir_url( __FILE__ ) );

require_once __DIR__.'/base/resources/install/helper.php';
//
$helper = \EasyDocLabs\EasyDoc\InstallHelper::getInstance(__FILE__);
//
//// Do not register the plugin if a FW install is pending
//
    if ($helper->canRegister())
    {

        require_once EASY_DOCS_PATH . 'foliokit/foliokit.php';

        \EasyDocLabs\WP::add_action('easydoc_before_bootstrap', function() {
            easydoc_register_plugin(__FILE__);
        });

        \EasyDocLabs\WP::add_action('easydoc_after_bootstrap', function()
        {
            try {
                $manager = \Foliokit::getObject('manager');

                $manager->setObject('user.provider', $manager->getObject('user.provider')->decorate('com:easydoc.user.provider'));
                $manager->setObject('user', $manager->getObject('user')->decorate('com:easydoc.user'));

                // Load the language file so menu items are translated in backend at all times
                \Foliokit::getObject('translator')->load('com:easydoc');

                // Sync role changes
                \EasyDocLabs\WP::add_action('updated_option', function($option_name, $old_roles, $new_roles) use ($manager)
                {
                    global $wpdb;

                    if ($option_name == sprintf('%suser_roles', $wpdb->prefix))
                    {
                        $model = $manager->getObject('com:easydoc.model.usergroups');

                        $usergroups = $model->internal(1)->fetch();

                        foreach ($usergroups as $usergroup)
                        {
                            if (!isset($new_roles[$usergroup->name]))
                            {
                                $usergroup->delete();
                                $usergroups->remove($usergroup);
                            }
                        }

                        foreach ($new_roles as $name => $role)
                        {
                            if (!$usergroups->find(array('name' => $name))->count()) {
                                $model->create(array('name' => $name, 'internal' => 1))->save();
                            }
                        }
                    }
                }, 10, 3);

                $sync_user = function($user_id, $hash_check = true) {
                    \Foliokit::getObject('com:easydoc.controller.user')->sync(['user_id' => $user_id, 'hash_check' => $hash_check]);
                };

                if ($user_id = \EasyDocLabs\WP::get_current_user_id()) {
                    $sync_user($user_id);  // Perform a "soft" roles/groups sync
                }

                // Sync user roles changes
                \EasyDocLabs\WP::add_action( 'set_user_role', function($user_id, $new_role, $old_roles) use ($sync_user) {
                    $sync_user($user_id);
                }, 10, 3 );

                // Sync user roles changes
                \EasyDocLabs\WP::add_action( 'add_user_role', function($user_id, $role) use ($sync_user) {
                    $sync_user($user_id);
                }, 10, 2 );

                // Sync user roles changes
                \EasyDocLabs\WP::add_action( 'remove_user_role', function($user_id, $role) use ($sync_user) {
                    $sync_user($user_id);
                }, 10, 2 );

                // Cleanup tables upon user deletes
                \EasyDocLabs\WP::add_action('deleted_user', function($user_id)
                {
                    $driver = \Foliokit::getObject('lib:database.driver.mysqli');

                    $query = \Foliokit::getObject('lib:database.query.delete')->table('easydoc_usergroups_users')
                                    ->where('wp_user_id = :user_id')->bind(['user_id' => $user_id]);

                    $driver->delete($query);

                    \Foliokit::getObject('com:easydoc.model.users')->id($user_id)->fetch()->delete(); // Delete corresponding user data
                }, 10, 1);


                $adminEditorView = function()
                {
                    global $hook_suffix;

                    $query = \Foliokit::getObject('request')->getQuery();
                    if ($query->get('component', 'cmd') !== 'easydoc' || $query->get('view', 'cmd') !== 'editor') {
                        return;
                    }

                    $hook_suffix = 'easydoc-form';

                    \EasyDocLabs\WP::add_filter('admin_footer_text', function () { return ''; }, PHP_INT_MAX, 1);
                    \EasyDocLabs\WP::add_filter('update_footer', function () { return ''; }, PHP_INT_MAX, 1);

                    \EasyDocLabs\WP::remove_action( 'in_admin_header', 'wp_admin_bar_render', 0 );
                    \EasyDocLabs\WP::add_filter( 'admin_title', function(){ $GLOBALS['wp_query']->is_embed=true;  add_action('admin_xml_ns', function(){ $GLOBALS['wp_query']->is_embed=false; } ); } );

                    $style = 'easydoc-admin-hide-sidebar';
                    \EasyDocLabs\WP::wp_register_style($style, '', []);
                    \EasyDocLabs\WP::wp_enqueue_style($style);
                    \EasyDocLabs\WP::wp_add_inline_style($style, "
                            #adminmenumain, #wpfooter { display: none; }
                            #wpcontent { margin: 5px; padding-left: 0; }
                            #wpbody-content { padding-bottom: 0; }
                            body { height: revert; /* avoid the scrollbar */ }
                            ");

                    require_once \EasyDocLabs\WP\ABSPATH . 'wp-admin/admin-header.php';

                    if (\Foliokit::getObject('request')->getQuery()->has('component')) {
                        // Dispatch action ran already. Return the result
                        echo \Foliokit::getObject('com:base.dispatcher.page')->render();
                    }

                    require_once \EasyDocLabs\WP\ABSPATH . 'wp-admin/admin-footer.php';
                    die;
                };

                $siteEditorView = function() {
                    if (is_admin()) { return; }

                    $query = \Foliokit::getObject('request')->getQuery();

                    // Dirty hack to support endpoint requests
                    if (strpos(urldecode(Foliokit::getObject('request')->getUrl()->getPath()), 'route:/editor') !== false) {
                        $query->set('component', 'easydoc');
                        $query->set('view', 'editor');

                        \Foliokit::getObject('com:base.dispatcher.page')->dispatch();
                    }

                    if ($query->get('component', 'cmd') !== 'easydoc' || $query->get('view', 'cmd') !== 'editor') {
                        return;
                    }

                    ?><!DOCTYPE html>
                    <html class="no-js" <?php language_attributes(); ?>>
                    <head>
                        <meta charset="<?php bloginfo( 'charset' ); ?>">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0" >
                        <link rel="profile" href="https://gmpg.org/xfn/11">
                        <?php wp_head(); ?>
                        <style>body { padding: 5px; }</style>
                    </head>
                    <body <?php body_class(); ?>>
                    <?php
                    wp_body_open();

                    if (\Foliokit::getObject('request')->getQuery()->has('component')) {
                        // Dispatch action ran already. Return the result
                        echo \Foliokit::getObject('com:base.dispatcher.page')->render();
                    }
                    wp_footer();
                    echo '</body></html>';
                    die();
                };

                \EasyDocLabs\WP::add_action('admin_init', $adminEditorView);
                \EasyDocLabs\WP::add_action('init', $siteEditorView, PHP_INT_MAX);

                // Siteground Optimizer script exclussions

                \EasyDocLabs\WP::add_filter( 'sgo_javascript_combine_exclude', function ($exclude_list)
                {
                    $exclude_list = array_merge($exclude_list, \EasyDocLabs\WP::getEnqueuedScripts());

                    return $exclude_list;
                });

                // Siteground Optimizer style exclussions

                \EasyDocLabs\WP::add_filter( 'sgo_css_combine_exclude', function($exclude_list)
                {
                    $exclude_list = array_merge($exclude_list, \EasyDocLabs\WP::getEnqueuedStyles());

                    return $exclude_list;
                });

                \EasyDocLabs\WP::add_action('permalink_structure_changed', function() use ($manager)
                {
                    // Force categories cache (tree) to re-generate itself

                    $manager->getObject('com:easydoc.database.table.categories')->clearCache();
                });

            } catch (\Exception $e){
                if (\Foliokit::isDebug()) throw $e;
            }
        });
    }
    else $helper->deactivate();
