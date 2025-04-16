<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

require_once ABSPATH . 'wp-admin/includes/plugin.php';

class InstallHelper
{
    const UPDATES_URL = 'https://api.system.ait-themes.club/extension/easydoc-wordpress.json?easydoclabs=1&plugin=easy-docs-ait';

    public $component;

    public $option_namespace;

    public $plugin;
    public $plugin_file;
    public $plugin_dir;
    public $plugin_basename;

    public $plugin_metadata;

    public $upgrader_options;

    public $wpdb;

	protected $_php;

    private static $_instance = null;

    private function __construct($pluginfile)
    {
        global $wpdb;

		$this->plugin_file     = $pluginfile;                             // e.g. ABSPATH/plugins/easydoclabs-todo/easydoclabs-todo.php
		$this->plugin          = basename($this->plugin_file, '.php');    // e.g. easydoclabs-todo
		$this->plugin_dir      = plugin_dir_path($pluginfile);        // e.g. ABSPATH/plugins/easydoclabs-todo
		$this->plugin_basename = plugin_basename($this->plugin_file); // e.g. easydoclabs-todo/easydoclabs-todo.php
		$this->_php            = '7.3';                                   // Minimum PHP version requirement

        $this->wpdb = $wpdb;

        if (($pos = strrpos($this->plugin, '-')) !== false) {
            $this->component = substr($this->plugin, $pos+1);
        } else {
            $this->component = $this->plugin;
        }

        $this->option_namespace = str_replace(['-', '.', ' '], '_', $this->plugin);

        $this->plugin_metadata = get_plugin_data($pluginfile, false, false);
    }

    public function getInstalledVersion() {
        return get_option($this->option_namespace.'_version');
    }

    public function getCurrentVersion() {
        return isset($this->plugin_metadata['Version']) ? $this->plugin_metadata['Version'] : null;
    }

	/**
	 * Admin message renderer
	 *
	 * @var array|string|\Throwable $message The error message
	 * @var string $title The error title
	 * @var array  $data An optional array containing config data
	 */
	public function renderMessage($message = '', $title = '', $data = [])
	{
		if (!$title) {
            // translators: Installer title
			$title = __( 'EasyDocs installer');
		}

		$html = '<div class="%s">';
		$html .= '<h1>' . htmlentities($title) . '</h1>';

		if ($message instanceof \Throwable) {
            // translators: Install/Upgrade error
			$message = sprintf(__('An error ocurred during the install/upgrade process of the plugin: "%1$s" on %2$s line %3$s'),
				$message->getMessage(), $message->getFile(), $message->getLine());
		}

		if (is_array($message))
		{
			$messages = $message;

			$html .= '<ul>';

			foreach ($messages as $message) {
				$html .= '<li>' . htmlentities($message) . '</li>';
			}

			$html .= '</ul>';
		}
		else $html .= '<p>' . htmlentities($message) . '</p>';

		$html .= '</div>';

		add_action('admin_notices', function() use ($html, $data)
		{
			if (!isset($data['type'])) $data['type'] = 'error';

			$class = sprintf('notice notice-%s is-dismissible', $data['type']);

			printf('<div class="%s">%s</div>', esc_attr($class), $html);
		});
	}

	/**
	 * Die with error message
	 *
	 * In addition to WP parameters that may be set in $data for the wp_die function, it is also possible to tell the error rendered to when and how
	 * the message should rendered:
	 *
	 * 'async': boolean When set to true we defer the error rendering depending on the 'embed' setting. If set to false, the error gets rendered
	 * 					right away and the execution stops.
	 * 'embed': boolean When set to true the error message is embeded within the WP admin template. If set to false, the error is rendered outside
	 * 					the template.
	 *
	 * @var string|\Throwable $message The error message
	 * @var string $title The error title
	 * @var array  $data An optional array containing data modifying the way the error gets rendered (@see wp_die at wp-includes/functions.php)
	 */
	public function die($message = '', $title = '', $data = [])
	{
		if (!isset($data['link_text']))
		{
			$data['link_url'] = admin_url() . 'plugins.php';
            // translators: Manage plugins link
			$data['link_text'] = sprintf('« %s', __('Manage plugins'));
		}

		if (!$title) {
            // translators: Installer title
			$title =  __( 'EasyDocs installer');
		}

		if ($message instanceof \Throwable) {
            // translators: Install/Upgrade error
			$message = sprintf(__('An error ocurred during the install/upgrade process of the plugin: "%1$s" on %2$s line %3$s'),
				$message->getMessage(), $message->getFile(), $message->getLine());
		}

		$html = "<h1>$title</h1>";
		$html .= "<p>$message</p>";

		if (!isset($data['embed'])) $data['embed'] = true;

		if (!isset($data['async'])) $data['async'] = true;

		if ($data['async'])
		{
			$action = $data['embed'] ? 'wp_after_admin_bar_render' : 'init';

			add_action($action, function() use ($html, $title, $data) {
				wp_die($html, $title, $data);

			},10, 0);
		}
		else wp_die($html, $title, $data); // Stop execution right away and display error
	}

    /**
     *
     * @param string $pluginfile
     * @return self
     */
    public static function getInstance($pluginfile = null)
    {
        if(is_null(self::$_instance))
        {
            if (is_null($pluginfile)) {
                throw new \RuntimeException('The plugin file argument is missing. A plugin file must be provided when instantiating the intall helper');
            }

            $helper = new self($pluginfile);

            $install = function($network_wide) use ($helper)
            {
                $easy_docs_ait_version = get_option('easy_docs_ait_version');
                if ($helper->needsInstall())
                {
                    $helper->run();
                }
//                elseif ($helper->hasFrameworkFolderToInstall())
//                {
//                    $helper->_installFramework();
//                }
                if ($helper->hasNewVersionTransient())
                {
                    $foliokitInstaller = function() use($helper)
					{
						if (class_exists('Foliokit') && $helper->runFoliokitDependentInstall()) {
							$helper->deleteNewVersionTransient();
						}
                    };

                    if (did_action('easydoc_after_bootstrap')) {
                        $foliokitInstaller();
                    } else {
                        add_action('easydoc_after_bootstrap', $foliokitInstaller);
                    }
                }

                if ($easy_docs_ait_version && $easy_docs_ait_version != get_option('easy_docs_ait_version'))
                {
                    wp_redirect(sprintf('%s/wp-admin/plugins.php', get_home_url())); // Redirect right away, the framework has been upgraded
                    exit();
                }

//                add_action('init', function() use($helper, $network_wide)
//                {
//                    // Defer Foliokit activation for preventing notices due to changes on WP 6.7.0
//
//                    $result = activate_plugin('foliokit/foliokit.php', '', $network_wide);
//
//                    if (is_wp_error($result)) {
//                        // translators: Cannot activate Foliokit
//                        $helper->abort($result->get_error_message(), __('Could not activate Foliokit'));
//                    }
//                });

                if ($helper->needsForceReinstall())
                {
                    wp_redirect(sprintf('%s/wp-admin/admin.php?component=easydoc&view=documents&page=easydoc-documents&reinstalled=1', get_home_url()));
                    exit();
                }
            };

            self::$_instance = $helper;

            if (is_admin())
            {
                add_action('plugins_loaded', function() use($install) {
                    $install(false);
                });

                add_action('wp_insert_site', function($site) use ($install)
                {
                    switch_to_blog($site->blog_id);
                    $install(false);
                    restore_current_blog();
                });

                add_action('wp_initialize_site', function($site) use ($helper)
                {
                    // We need to wait out until WP finishes up creating all the site tables for us to set the installed version option and new version transient

                    switch_to_blog($site->blog_id);

                    $helper->setInstalledVersion($helper->getCurrentVersion());
                    $helper->runFoliokitDependentInstall(); // We run this manually and we do it now, since transients are not being stored during site creation for some reason

                    restore_current_blog();
                },999);

                add_action('wp_uninitialize_site', function($site) use ($helper)
                {
                    switch_to_blog($site->blog_id);
                    $helper->uninstall(true); // Tell the un-installer we are removing a site
                    restore_current_blog();
                });

                register_uninstall_hook($pluginfile, array('\EasyDocLabs\EasyDoc\InstallHelper', 'uninstall'));
            }

            register_activation_hook($pluginfile, function($network_wide) use($install, $helper)
			{
				try
				{
					if (is_multisite() && $network_wide)
					{
						global $wpdb;

						foreach($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $blog_id)
						{
							switch_to_blog($blog_id);
							$install($network_wide);
						}

						restore_current_blog();
					}
					else $install($network_wide);
				}
				catch (\Throwable $e)
				{
					if (WP_DEBUG) {
						throw $e;
					} else {
						$helper->abort($e);
					}
				}
            });

            add_action('activate_plugin', function($plugin) use ($helper)
            {
                if (!$helper->isPhpValid())
                {
                    // translators: Cannot activate title
                    $title = __( 'EasyDocs Pro cannot be activated');

                    // translators: PHP version not supported
					$message = sprintf(__('Your server is running PHP %1$s which is not supported by EasyDocs. Please upgrade to PHP %2$s or higher.'),
						phpversion(), $helper->getValidPhp());

					$helper->die($message, $title, ['response' => 403, 'async' => false]);
                }

                if ($helper->isFreeVersionInstalled())
                {
                    // translators: Cannot activate title
                    $title = __( 'EasyDocs Pro cannot be activated');

                    // translators: Cannot activate description
					$message = __('We have detected that you have EasyDocs Free enabled in your website. Please disable it first before activating this version.');

					$helper->die($message, $title, ['response' => 403, 'async' => false]);
                }
            });

            add_filter('upgrader_package_options', function($options) use ($helper)
            {
                $helper->upgrader_options = $options; // Store upgrader data for later use

                return $options;
            }, 10, 2);

            add_filter( 'upgrader_pre_install', function($response, $data) use ($helper)
            {
                global $wp_filesystem;

                $upgrader_options = $helper->upgrader_options;

                if ($data['type'] == 'plugin' && $data['action'] == 'install')
                {
                    $folder = sprintf('%s/%s', dirname($upgrader_options['package']), '.easy-docs-ait-tmp');

                    if ($wp_filesystem->is_dir($folder)) {
                        $wp_filesystem->delete($folder, true);
                    }

                    unzip_file( $upgrader_options['package'], $folder);

                    $plugin_file = sprintf('%s/easy-docs-ait/%s', $folder, 'easy-docs-ait.php');

                    if (file_exists($plugin_file) && $helper->getCurrentVersion())
                    {
                        $metadata = get_plugin_data($plugin_file, false, false);

                        if (version_compare($helper->getCurrentVersion(), $metadata['Version'], '>'))
                        {
                            // translators: Downgrades not allowed
                            $response = new \WP_Error(403, sprintf(__('Downgrades are not allowed. The installer
                            attempted to install EasyDocs v%1$s, which is an earlier version than the one installed on your
                            site (v%2$s).'), $metadata['Version'], $helper->getCurrentVersion()));
                        }

                        if (!$response instanceof \WP_Error)
                        {
                            if(!$helper->isPhpValid()) {
                                // translators: Unsupported PHP version
								$response = new \WP_Error(403, sprintf(__('Your server is running PHP %1$s which is not supported by EasyDocs. Please upgrade to PHP %2$s or higher.'),
									phpversion(), $this->getValidPhp()));
                            }
                        }
                    }

                    $wp_filesystem->delete($folder, true);
                }

                return $response;
            }, 10, 2);

            require __DIR__ . '/autoupdate/Puc/EasyDoc/Autoloader.php';

			new \Puc_easydoc_Autoloader();

            new \Puc_easydoc_Plugin_UpdateChecker(static::UPDATES_URL, $pluginfile);
        }

        return self::$_instance;
    }

	public function getValidPhp()
	{
		return $this->_php;
	}

    public function isPhpValid()
    {
        return !!version_compare(phpversion(), $this->getValidPhp(), '>');
    }

    public function canRegister()
    {
        return ($this->isPhpValid() /*&& !$this->isFreeVersionInstalled()*/);
    }

    public function isFreeVersionInstalled($active = true)
    {
        $plugins = $active ? get_option('active_plugins') : array_keys(get_plugins());

        if ($plugins === false) $plugins = [];

        return in_array('easy-docs-ait-free/easy-docs-ait-free.php', $plugins);
    }

    public function runFoliokitDependentInstall()
    {
        try
		{
            /** @var \EasyDocLabs\EasyDoc\License $license */
            $license = \Foliokit::getObject('license');
            $license->onInstall();
		}
		catch (\Throwable $e)
		{
            if (WP_DEBUG) throw $e; // We don't report anything here as we don't want blocks due to license originated exceptions
		}

        try
		{
			$this->_createFilesContainer();
			$this->_createImagesContainer();
			$this->_createIconsContainer();

			if (!get_option('easydoc_roles_synced'))
			{
				\Foliokit::getObject('com://admin/easydoc.controller.user')->sync(['reset' => true]);

				add_option('easydoc_roles_synced', 1);
			}

			if (!get_option('easydoc_initial_data_added')) {
				\Foliokit::getObject('com://admin/easydoc.controller.category', array(
					'behaviors' => array(
						'permissible' => array(
							'permission' => 'com:/easydoc.controller.permission.yesman'
						)
					)
				))->add(['title' => 'Uncategorized',
						'automatic_folder' => 1]);


				add_option('easydoc_initial_data_added', 1);
			}

			if (!get_option('easydoc_default_config'))
			{
				// Set default config options
				$config = \Foliokit::getObject('com:easydoc.model.entity.config');

				$config->can_create_tag = 1;
				$config->permissions = $this->_getPermissions();
				$config->save();

				$config->clearCache();

				add_option('easydoc_default_config', 1);
			}

			if (!get_option('easydoc_permissions_migrated')) {
				$this->_migratePermissions();
			}

			if (!get_option('easydoc_document_view_permissions_sync')) {
				$this->_syncDocumentViewPermissions();
			}

			return true;
        }
        catch (\Throwable $e)
		{
            if (WP_DEBUG) {
				throw $e;
			} else {
				$this->abort($e);
			}
        }
    }

    protected function _syncDocumentViewPermissions()
    {
        $config = \Foliokit::getObject('com:easydoc.model.entity.config');

        $permissions = $config->permissions;

        if (!isset($permissions['view_document']))
        {
            // Start by syncing global permissions

            $permissions['view_category']     = $permissions['view'];
            $permissions['view_document']     = $permissions['view'];
            $permissions['upload_document']   = $permissions['upload'];
            $permissions['download_document'] = $permissions['download'];

            unset($permissions['view']);
            unset($permissions['upload']);
            unset($permissions['download']);

            $config->permissions = $permissions;

            $config->save();

            // Sync permissions rows

            $query       = sprintf("SELECT * FROM `%seasydoc_permissions` WHERE `table` = %%s", $this->wpdb->prefix);
            $permissions = $this->wpdb->get_results($this->wpdb->prepare($query, 'easydoc_categories'));

            foreach ($permissions as $permission)
            {
                $data = json_decode($permission->data, true);

                foreach ($data as $type => &$actions)
                {
                    if (isset($actions['view']))
                    {
                        // Use same groups for each view action

                        $actions['view_category'] = $actions['view'];
                        $actions['view_document'] = $actions['view'];

                        unset($actions['view']);
                    }

                    if (isset($actions['download']))
                    {
                        $actions['download_document'] = $actions['download'];
                        unset($actions['download']);
                    }

                    if (isset($actions['upload']))
                    {
                        $actions['upload_document'] = $actions['upload'];
                        unset($actions['upload']);
                    }
                }

                $query = "UPDATE `#__easydoc_permissions` SET `data` = %s WHERE `table` = %s AND `row` = %s";

                $this->_executeQuery($this->wpdb->prepare($query, \EasyDocLabs\WP::wp_json_encode($data), $permission->table, $permission->row));
            }

            $config->clearCache(); // Re-generate transient permissions
        }

        add_option('easydoc_document_view_permissions_sync', 1);
    }

    protected function _migratePermissions()
    {
        $query       = sprintf("SELECT * FROM `%seasydoc_permissions` WHERE `table` = %%s", $this->wpdb->prefix);
        $permissions = $this->wpdb->get_results($this->wpdb->prepare($query, 'easydoc_categories'));

        $actions_map = [
            'add'    => ['add_category', 'upload'],
            'edit'   => ['edit_category', 'edit_document'],
            'delete' => ['delete_category', 'delete_document']
        ];

        foreach ($permissions as $permission)
        {
            $data = json_decode($permission->data, true);

            foreach ($data as $type => &$actions)
            {
                foreach ($actions as $action => $value)
                {
                    if (isset($actions_map[$action]))
                    {
                        foreach ($actions_map[$action] as $mapped_action) $actions[$mapped_action] = $value;

                        unset($actions[$action]);
                    }
                }
            }

            $query = "UPDATE `#__easydoc_permissions` SET `data` = %s WHERE `table` = %s AND `row` = %s";

            $this->_executeQuery($this->wpdb->prepare($query, \EasyDocLabs\WP::wp_json_encode($data), $permission->table, $permission->row));
        }

        // Migrate default permissions

        $settings = get_option('easydoc_options');

        if (isset($settings['permissions']))
        {
            $permissions = $settings['permissions'];

            foreach ($permissions as $action => $value)
            {
                if (isset($actions_map[$action]))
                {
                    foreach ($actions_map[$action] as $mapped_action) $permissions[$mapped_action] = $value;

                    unset($permissions[$action]);
                }
            }

            $settings['permissions'] = $permissions;

            update_option('easydoc_options', $settings);
        }

		\Foliokit::getObject('easydoc.users')->clearPermissions();

        add_option('easydoc_permissions_migrated', 1);
    }

    public function hasFrameworkFolderToInstall() {
        return is_dir($this->plugin_dir.'/framework');
    }

    protected function _getWpContentDir()
    {
        $wp_content_dir = WP_CONTENT_DIR;

        if (strpos($wp_content_dir, ABSPATH) === 0) {
            $wp_content_dir = substr($wp_content_dir, strlen(ABSPATH));
        }

        $wp_content_dir = rtrim($wp_content_dir, "/\\");

        return $wp_content_dir;

    }

    protected function _getContainerSuffix()
    {
        if (function_exists('is_multisite') && is_multisite()) {
            return get_current_blog_id();
        }

        return '';
    }

    protected function _createFilesContainer()
    {
        $entity = \Foliokit::getObject('com:files.model.containers')->slug('easydoc-files')->fetch();
        $path   = $this->_getWpContentDir().'/easydoclabs/easydoc-files'.$this->_getContainerSuffix();

        if ($entity->isNew())
        {
            $thumbnails = true;

            if (!extension_loaded('gd'))
            {
                $thumbnails = false;
                $translator = \Foliokit::getObject('translator');
                \Foliokit::getObject('response')->addMessage(
                    $translator->translate('Your server does not have the necessary GD image library for thumbnails.')
                );
            }

            $extensions = explode(',', 'csv,doc,docx,html,key,keynote,odp,ods,odt,pages,pdf,pps,ppt,pptx,rtf,tex,txt,xls,xlsx,xml,7z,ace,bz2,dmg,gz,rar,tgz,zip,bmp,exif,gif,ico,jpeg,jpg,png,psd,tif,tiff,aac,aif,aiff,alac,amr,au,cdda,flac,m3u,m3u,m4a,m4a,m4p,mid,mp3,mp4,mpa,ogg,pac,ra,wav,wma,3gp,asf,avi,flv,m4v,mkv,mov,mp4,mpeg,mpg,ogg,rm,swf,vob,wmv');

            $entity->create([
                'slug' => 'easydoc-files',
                'path' => $path,
                'title' =>  'EasyDocs',
                'parameters' => [
                    'allowed_extensions' => $extensions,
                    'maximum_size' => 0,
                    'thumbnails' => $thumbnails
                ]
            ]);

            if ($entity->save()) {
                $entity->disablePublicAccess();
            }
        }
    }

    protected function _createIconsContainer()
    {
        $entity = \Foliokit::getObject('com:files.model.containers')->slug('easydoc-icons')->fetch();
        $path = $this->_getWpContentDir().'/easydoclabs/easydoc-icons'.$this->_getContainerSuffix();

        if ($entity->isNew())
        {
            $entity->create([
                'slug' => 'easydoc-icons',
                'path' => $path,
                'title' =>  'EasyDocs Icons',
                'parameters' => [
                    'allowed_extensions' => explode(',', 'bmp,gif,jpeg,jpg,png'),
                    'allowed_mimetypes' => ["image/jpeg", "image/gif", "image/png", "image/bmp"],
                    'maximum_size' => 0,
                    'thumbnails' => true
                ]
            ]);
            $entity->save();
        }
    }

    protected function _createImagesContainer()
    {
        $entity = \Foliokit::getObject('com:files.model.containers')->slug('easydoc-images')->fetch();
        $path = $this->_getWpContentDir().'/easydoclabs/easydoc-images'.$this->_getContainerSuffix();

        if ($entity->isNew())
        {
            $entity->create([
                'slug' => 'easydoc-images',
                'path' => $path,
                'title' =>  'EasyDocs Images',
                'parameters' => [
                    'allowed_extensions' => explode(',', 'bmp,gif,jpeg,jpg,png'),
                    'allowed_mimetypes'  => ["image/jpeg", "image/gif", "image/png", "image/bmp"],
                    'maximum_size'       => 0,
                    'thumbnails'         => false
                ]
            ]);

            $entity->save();
        }
    }

    public function needsForceReinstall()
    {
        return is_admin() && $this->component && isset($_GET['component']) && $_GET['component'] === $this->component && isset($_GET['reinstall']) && $_GET['reinstall'];
    }


    public function needsInstall()
    {
        if (!$this->getInstalledVersion() || ($this->getCurrentVersion() !== $this->getInstalledVersion()) /*|| $this->needsForceReinstall()*/) {
            return true;
        }
    }

    public function setInstalledVersion($version)
    {
        update_option($this->option_namespace.'_version', $version);

        $this->setNewVersionTransient($version);
    }

    public function setNewVersionTransient($value = true)
    {
        return set_transient($this->option_namespace.'_installed', $value);
    }

    public function hasNewVersionTransient()
    {
        return get_transient($this->option_namespace.'_installed');
    }

    public function deleteNewVersionTransient()
    {
        return delete_transient($this->option_namespace.'_installed');
    }

    public function abort($error)
    {
//        $this->renderMessage($error);
//        $this->deactivate();
    }

    public function deactivate()
    {
        deactivate_plugins($this->plugin_basename);
    }

    public function run()
    {
		try
		{
			@set_time_limit(@ini_get('max_execution_time'));
			@ini_set('memory_limit', '256M');
			@ini_set('memory_limit', '512M');

			$errors = [];

			$privileges = $this->getRequiredDatabasePrivileges();

			if ($privileges && ($failed = $this->_checkDatabasePrivileges($privileges)))
			{
				$errors[] = sprintf('The following MySQL privileges are missing: %s. Please make them available to your MySQL user and try again.',
					htmlspecialchars(implode(', ', $failed), ENT_QUOTES));
			}

			if ($system_errors = $this->getSystemErrors()) {
				$errors = array_merge($errors, $system_errors);
			}

			if (empty($errors))
			{
//				$this->_installFramework();

				if (($this->getInstalledVersion() && ($this->getCurrentVersion() !== $this->getInstalledVersion())) || $this->needsForceReinstall()) {
					$this->update();
				} else {
					$this->install();
				}

				self::clearCache();

				$this->setInstalledVersion($this->getCurrentVersion());
			}
			else $this->abort($errors);
		}
		catch(\Throwable $e)
		{
			if (WP_DEBUG) {
				throw $e;
			} else {
				$this->abort($e);
			}
		}
    }

    public function install()
    {
        $install_sql = $this->plugin_dir.'/base/resources/install/install.sql';
        $update_sql  = $this->plugin_dir.'/base/resources/install/update.sql';

        if (file_exists($install_sql)) {
            $this->_executeSqlFile($install_sql);
        }

        if (file_exists($update_sql)) {
            $this->_executeSqlFile($update_sql);
        }
    }

    public function update()
    {
        $this->_migrate();

        $this->install();

        // 1.0.0-beta.1 Fix block namespace in earlier alpha versions
        $this->_executeQuery("UPDATE `#__posts` set post_content = REPLACE(`post_content`, 'wp:easydoc-block/', 'wp:easydoc/')");

        // 1.0.0-beta.2 Fix changed block and shortcode names
        $this->_executeQuery("UPDATE `#__posts` set post_content = REPLACE(`post_content`, 'wp:easydoc/doclink', 'wp:easydoc/attachments')");
        $this->_executeQuery("UPDATE `#__posts` set post_content = REPLACE(`post_content`, 'wp:easydoc/list {\"layout\":\"default\"', 'wp:easydoc/list {\"layout\":\"list\"')");
        $this->_executeQuery("UPDATE `#__posts` set post_content = REPLACE(`post_content`, '[easydoc-doclink', '[easydoc-attachments')");
        $this->_executeQuery("UPDATE `#__posts` set post_content = REPLACE(`post_content`, '[easydoc-list', '[easydoc')");

        // 1.2.0 Support document view permissions

        if ($this->_tableExists('easydoc_categories_usergroups'))
        {
            $this->_executeQuery('INSERT IGNORE INTO `#__easydoc_category_usergroup_access` (`easydoc_category_id`, `easydoc_usergroup_id`) SELECT `easydoc_category_id`, `easydoc_usergroup_id` FROM `#__easydoc_categories_usergroups`');
            $this->_executeQuery('INSERT IGNORE INTO `#__easydoc_document_usergroup_access` (`easydoc_category_id`, `easydoc_usergroup_id`) SELECT `easydoc_category_id`, `easydoc_usergroup_id` FROM `#__easydoc_categories_usergroups`');
            $this->_executeQuery('DROP TABLE `#__easydoc_categories_usergroups`');
        }

        if ($this->_tableExists('easydoc_categories_users'))
        {
            $this->_executeQuery('INSERT IGNORE INTO `#__easydoc_category_user_access` (`easydoc_category_id`, `wp_user_id`) SELECT `easydoc_category_id`, `wp_user_id` FROM `#__easydoc_categories_users`');
            $this->_executeQuery('INSERT IGNORE INTO `#__easydoc_document_user_access` (`easydoc_category_id`, `wp_user_id`) SELECT `easydoc_category_id`, `wp_user_id` FROM `#__easydoc_categories_users`');
            $this->_executeQuery('DROP TABLE `#__easydoc_categories_users`');
        }

        if ($this->_columnExists('easydoc_categories', 'access_usergroups'))
        {
            $this->_executeQuery("ALTER TABLE `#__easydoc_categories` CHANGE `access_usergroups` `inherit_category_usergroup_access` bigint(20) unsigned");
            $this->_executeQuery("ALTER TABLE `#__easydoc_categories` CHANGE `access_users` `inherit_category_user_access` bigint(20) unsigned");
            $this->_executeQuery("ALTER TABLE `#__easydoc_categories` ADD `inherit_document_user_access` bigint(20) unsigned DEFAULT NULL AFTER `inherit_category_usergroup_access`");
            $this->_executeQuery("ALTER TABLE `#__easydoc_categories` ADD `inherit_document_usergroup_access` bigint(20) unsigned DEFAULT NULL AFTER `inherit_document_user_access`" );
            $this->_executeQuery("UPDATE `#__easydoc_categories` SET `inherit_document_user_access` = `inherit_category_user_access`");
            $this->_executeQuery("UPDATE `#__easydoc_categories` SET `inherit_document_usergroup_access` = `inherit_category_usergroup_access`");
            $this->_executeQuery("ALTER TABLE `#__easydoc_categories` CHANGE `permission` `inherit_permissions` bigint(20) unsigned");
        }

        if ($this->_columnExists('easydoc_categories', 'manager')) {
            $this->_executeQuery("ALTER TABLE `#__easydoc_categories` DROP COLUMN `manager`");
        }

        if ($this->_tableExists('easydoc_categories_managers')) {
            $this->_executeQuery('DROP TABLE `#__easydoc_categories_managers`');
        }

		// 1.3.0

		if ($this->_tableExists('easydoc_category_usergroup_access'));
        {
			$this->_executeQuery('ALTER TABLE `#__easydoc_category_usergroup_access` RENAME `#__easydoc_category_group_access`');
            $this->_executeQuery("ALTER TABLE `#__easydoc_categories` CHANGE `inherit_category_usergroup_access` `inherit_category_group_access` bigint(20) unsigned");
			$this->_executeQuery("ALTER TABLE `#__easydoc_categories` DROP COLUMN `inherit_category_user_access`");
		}

		if ($this->_tableExists('easydoc_document_usergroup_access'));
        {
			$this->_executeQuery('ALTER TABLE `#__easydoc_document_usergroup_access` RENAME `#__easydoc_document_group_access`');
            $this->_executeQuery("ALTER TABLE `#__easydoc_categories` CHANGE `inherit_document_usergroup_access` `inherit_document_group_access` bigint(20) unsigned");
			$this->_executeQuery("ALTER TABLE `#__easydoc_categories` DROP COLUMN `inherit_document_user_access`");
		}

		if ($this->_tableExists('easydoc_category_user_access')) {
			$this->_executeQuery('DROP TABLE `#__easydoc_category_user_access`');
		}

		if ($this->_tableExists('easydoc_document_user_access')) {
			$this->_executeQuery('DROP TABLE `#__easydoc_document_user_access`');
		}

		if ($this->_tableExists('easydoc_permissions_transients')) {
			$this->_executeQuery('DROP TABLE `#__easydoc_permissions_transients`');
		}

        // 1.4.1

        $this->_executeQuery('ALTER TABLE `#__easydoc_usergroups` CHANGE `internal` `internal` tinyint(1) NOT NULL DEFAULT 0');

        // 2.2.1

        if (!get_option('easydoc_sync_config_fixed', 0) && ($this->getCurrentVersion() === '2.2.1' && $this->getInstalledVersion() === '2.2.0'))
        {
            // Disable auto files and folders sync

            $config = \Foliokit::getObject('com:easydoc.model.entity.config');

            $config->automatic_category_creation = false;
            $config->automatic_document_creation = false;

            $config->save();

            add_option('easydoc_sync_config_fixed', 1);
        }
    }

    public static function uninstall($multisite = false)
    {
		try
		{
            // Only delete DB data if EasyDocs Free is not installed OR is we are deleting a multisite site

            if (!self::$_instance->isFreeVersionInstalled(false) || $multisite)
            {
                // TODO List all options to get deleted, some are still missing

                delete_option('easydoc_usergroups_synced');
                delete_option('easydocs_easydoc_version');
                delete_option('easydoc_initial_data_added');
                delete_option('easydoc_options');
                delete_option('easydoc_default_config');
                delete_option('external_updates-easy-docs-ait');
                delete_option('easydoc_permissions_migrated');
                delete_option('easydoc_document_view_permissions_sync');
                delete_option('easydoc_usergroups_synced');
                delete_option('easydoc_roles_synced');

                self::clearCache();

                $files = ['uninstall.sql'];

                if ($multisite) {
                   $files[] = '/multisite_uninstall.sql'; // Delete FW related tables for the current site
                }

                foreach ($files as $file)
                {
                    $file = __DIR__ . '/' . $file;
                    if (file_exists($file)) {
                        self::$_instance->_executeSqlFile($file);
                    }
                }
            }

            delete_option('easydoc_installed');
		}
		catch (\Throwable $e)
		{
			if (!WP_DEBUG)
			{
                // translators: Install/Upgrade error
				$message = sprintf(__('An error ocurred during the install/upgrade process of the plugin: "%1$s" on %2$s line %3$s'),
					$e->getMessage(), $e->getFile(), $e->getLine());

                    // translators: Un-installer title
					self::$_instance->renderMessage($message, __( 'EasyDocs un-installer'));
			}
			else throw $e;
		}
    }

    public function getSystemErrors()
    {
        $errors = [];

        if(version_compare(phpversion(), $this->_php, '<'))
        {
            $errors[] = sprintf('Your server is running PHP %s which is an old and insecure version.
            It also contains a bug affecting the operation of our extensions.
            Please contact your host and ask them to upgrade PHP to at least %s version on your server.', phpversion(), $this->_php);
        }

        if (!function_exists('token_get_all')) {
            $errors[] = 'PHP tokenizer extension must be enabled by your host.';
        }

        return $errors;
    }

    public function getRequiredDatabasePrivileges()
    {
        return [];
    }

    protected function _installFramework()
    {
		try
		{
			$bundled_installer   = $this->plugin_dir.'/framework/component/base/resources/install/install.php';
			$existing_installer  = WP_PLUGIN_DIR.'/foliokit/component/base/resources/install/install.php';

			foreach ([$bundled_installer, $existing_installer] as $installer_file)
			{
				if (is_file($installer_file)) {
					$installer = require $installer_file;

					if (is_callable($installer)) {
						$installer();
						break;
					}
				}
			}
		}
		catch(\Throwable $e)
		{
			if (WP_DEBUG) {
				throw $e;
			} else {
				$this->abort($e);
			}
		}
    }

    protected function _deleteFolder($src)
	{
		$dir = opendir($src);

		while(false !== ( $file = readdir($dir)) )
		{
			if (( $file != '.' ) && ( $file != '..' ))
			{
				$full = $src . '/' . $file;

				if ( is_dir($full) ) {
					$this->_deleteFolder($full);
				} else {
                    self::getFilesystem()->delete($full);
				}
			}
		}

		closedir($dir);

		self::getFilesystem()->rmdir($src);

		return true;
    }

    protected function _moveFolder($src, $dest)
    {
        if (!is_dir($src) || is_dir($dest)) {
            return false;
        }

        return @self::getFilesystem()->move($src, $dest);
    }

    protected function _moveFile($src, $dest)
    {
        if (!file_exists($src)) {
            return false;
        }

        if (!is_dir(dirname($dest))) {
            if (!@self::getFilesystem()->mkdir(dirname($dest))) return false;
        }

        return @self::getFilesystem()->move($src, $dest, true);
    }

    protected function _copyFolder($src, $dest)
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

        if (!self::getFilesystem()->mkdir($dest)) {
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
                        $ret = $this->_copyFolder($sfid, $dfid);

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
    }

    protected function _moveFolderWithBackup($from, $to)
    {
		$from = rtrim($from, '/');
		$to   = rtrim($to, '/');

		$temp = $to . '_tmp';
		$bkp  = $to . '_bkp';

        if (is_dir($temp))
		{
            if (!$this->_deleteFolder($temp) || is_dir($temp)) {
                return false;
            }
        }

        if ($this->_copyFolder($from, $temp))
        {
            if (is_dir($to))
			{
                if (!$this->_copyFolder($to, $bkp)) {
                    return false;
                }

                if (!$this->_deleteFolder($to) || is_dir($to)) {
                    return false;
                }
            }

            $result = @self::getFilesystem()->move($temp, $to);

            if ($result)
			{
                if (is_dir($from)) {
                    $this->_deleteFolder($from);
                }

                if (is_dir($bkp)) {
                    $this->_deleteFolder($bkp);
                }
            }
        }

        return true;
    }

    protected function _deleteOldFiles($nodes = [])
    {
        foreach ($nodes as $node)
        {
            $path = ABSPATH.'/'.$node;

            if (file_exists($path))
			{
                if (is_dir($path)) {
                    $this->_deleteFolder($path);
                } else {
                    self::getFilesystem()->delete($path);
                }
            }
        }

        return true;
    }

    protected function _executeQuery($query)
	{
        return $this->wpdb->query($this->_replacePrefix($query));
    }

    protected function _executeQueries($queries)
    {
        if (is_string($queries)) {
            $queries = $this->_splitSql($queries);
        }

        foreach ($queries as $query) {
            $this->_executeQuery($query);
        }
    }

    protected function _executeSqlFile($file)
    {
        $buffer = self::getFilesystem()->get_contents($file);

        if ($buffer !== false) {
            $this->_executeQueries($buffer);
        }
    }

    protected function _replacePrefix($sql, $prefix = '#__')
    {
		$escaped   = false;
		$startPos  = 0;
		$quoteChar = '';
		$literal   = '';
		$sql       = trim($sql);
		$n         = \strlen($sql);

        while ($startPos < $n)
        {
            $ip = strpos($sql, $prefix, $startPos);

            if ($ip === false) {
                break;
            }

            $j = strpos($sql, "'", $startPos);
            $k = strpos($sql, '"', $startPos);

            if (($k !== false) && (($k < $j) || ($j === false)))
            {
                $quoteChar = '"';
                $j         = $k;
            }
            else $quoteChar = "'";

            if ($j === false){
                $j = $n;
            }

            $literal .= str_replace($prefix, $this->wpdb->prefix, substr($sql, $startPos, $j - $startPos));

			$startPos = $j;
			$j        = $startPos + 1;

			if ($j >= $n) {
                break;
            }

            // Quote comes first, find end of quote

			while (true)
            {
                $k       = strpos($sql, $quoteChar, $j);
                $escaped = false;

				if ($k === false) {
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

            if ($k === false) {
                break;  // Error in the query - no end quote; ignore it
            }

            $literal .= substr($sql, $startPos, $k - $startPos + 1);

			$startPos = $k + 1;
        }

        if ($startPos < $n) {
            $literal .= substr($sql, $startPos, $n - $startPos);
        }

        return $literal;
    }

    protected function _tableExists($table)
    {
        if (substr($table, 0,  3) !== '#__') {
            $table = '#__'.$table;
        }

        $table = str_replace('#__', $this->wpdb->prefix, $table);

        return (bool) $this->wpdb->get_var($this->wpdb->prepare('SHOW TABLES LIKE %s', $table));
    }

    protected function _columnExists($table, $column)
    {
        $result = false;

        if ($this->_tableExists($table))
        {
            if (substr($table, 0,  3) !== '#__') {
                $table = '#__'.$table;
            }

            $table = str_replace('#__', $this->wpdb->prefix, $table);

            $query  = 'SHOW COLUMNS FROM '.$table.' WHERE Field = %s';

            $result = (bool) $this->wpdb->get_var($this->wpdb->prepare($query, $column));
        }

        return $result;
    }

    protected function _constraintExists($table, $name)
    {
        $result = false;

        if ($this->_tableExists($table))
        {
            if (substr($name, 0,  3) !== '#__') {
                $name = '#__'.$name;
            }

            $name = str_replace('#__', $this->wpdb->prefix, $name);

            $query  = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = %s AND CONSTRAINT_NAME = %s";

			$result = (bool) $this->wpdb->get_var($this->wpdb->prepare($query, $this->wpdb->dbname, $name));
        }

        return $result;
    }

    protected function _indexExists($table, $index_name)
    {
        $result = false;

        if ($this->_tableExists($table))
        {
            if (substr($table, 0,  3) !== '#__') {
                $table = '#__'.$table;
            }

            $table = str_replace('#__', $this->wpdb->prefix, $table);

            $query  = 'SHOW KEYS FROM '.$table.' WHERE Key_name = %s';

			$result = (bool) $this->wpdb->get_var($this->wpdb->prepare($query, $index_name));
        }

        return $result;
    }

    protected function _getPermissions()
    {
        $permissions_mapping = [
            'view_category'     => ModelEntityUsergroup::FIXED['public']['id'],
            'add_category'      => 'edit_published_posts',
            'edit_category'     => 'edit_published_posts',
            'delete_category'   => 'delete_published_posts',
            'view_document'     => ModelEntityUsergroup::FIXED['public']['id'],
            'upload_document'   => 'edit_published_posts',
            'edit_document'     => 'edit_published_posts',
            'delete_document'   => 'delete_published_posts',
            'download_document' => ModelEntityUsergroup::FIXED['public']['id']
        ];

        $permissions = [];

        $usergroups = \Foliokit::getObject('com:easydoc.model.usergroups')->internal(1)->hide_admin(true)->fetch();

        foreach (ModelEntityPermission::getActions() as $action)
        {
            if (!is_numeric($permissions_mapping[$action]))
            {
                $permissions[$action] = [];

                foreach ($usergroups as $usergroup)
                {
                    $role = $usergroup->getRole();

                    if ($role->has_cap($permissions_mapping[$action])) {
                        $permissions[$action][] = $usergroup->id;
                    }
                }
            }
            else $permissions[$action] = [$permissions_mapping[$action]];
        }

        return $permissions;
    }

    protected function _backupTable($table)
    {
        if ($this->_tableExists($table))
        {
            $destination = $table.'_bkp';

            if ($this->_tableExists($destination))
            {
                $i = 2;

                while (true)
                {
                    if (!$this->_tableExists($destination.$i)) {
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

            $table       = str_replace('#__', $this->wpdb->prefix, $table);
            $destination = str_replace('#__', $this->wpdb->prefix, $destination);

            $return = $this->wpdb->query(sprintf('RENAME TABLE `%1$s` TO `%2$s`;', $table, $destination));
        }
        else $return = true;

        return $return;
    }

    public static function clearCache()
    {
        // Clear APC opcode cache
        if ( extension_loaded('apcu') && apcu_enabled()) {
            apcu_clear_cache();
        }

        // Clear OPcache
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
    }

    /**
     * WP Filesystem API instance getter
     *
     * We need our own here since Foliokit instance getter might not be ready from older upgrades
     *
     * @return WP_Filesystem
     */
    public static function getFilesystem()
    {
        global $wp_filesystem;

        // Make sure that the above variable is properly setup.
        require_once ABSPATH . 'wp-admin/includes/file.php';

        WP_Filesystem();

        return $wp_filesystem;
    }

    /**
     * Tests a list of DB privileges against the current application DB connection.
     *
     * @param array $privileges An array containing the privileges to be checked.
     *
     * @return array True An array containing the privileges that didn't pass the test, i.e. not granted.
     */
    protected function _checkDatabasePrivileges($privileges)
    {
        $privileges = (array) $privileges;

        $sql_mode = $this->wpdb->get_var('SELECT @@SQL_MODE');

        // Quote and escape DB name.
        if (strtolower($sql_mode) == 'ansi_quotes') {
            // Double quotes as delimiters.
            $db_name = '"' . str_replace('"', '""', DB_NAME) . '"';
        } else {
            $db_name = '`' . str_replace('`', '``', DB_NAME) . '`';
        }

        // Properly escape DB name.
        $possible_tables = [
            '*.*',
            $db_name . '.*',
            strtolower($db_name . '*'),
            str_replace('_', '\_', $db_name) . '.*',
            strtolower(str_replace('_', '\_', $db_name) . '.*')
        ];

        $grants = $this->wpdb->get_col('SHOW GRANTS');
        $granted = [];

        foreach ($grants as $grant)
        {
            if (stripos($grant, 'USAGE ON') === false)
            {
                foreach ($privileges as $privilege)
                {
                    $regex = '/(grant\s+|,\s*)' . $privilege . '(\s*,|\s+on)/i';

                    if (stripos($grant, 'ALL PRIVILEGES') || preg_match($regex, $grant))
                    {
                        // Check tables
                        $tables = substr($grant, stripos($grant, ' ON ') + 4);
                        $tables = substr($tables, 0, stripos($tables, ' TO'));
                        $tables = trim($tables);

                        if (in_array($tables, $possible_tables)) {
                            $granted[] = $privilege;
                        }
                    }
                }
            }
            else
            {
                // Proceed with installation if user is granted USAGE
                $granted = $privileges;
                break;
            }
        }

        return array_diff($privileges, $granted);
    }

    protected function _migrate()
    {
        $old_constraints = [
            '#__easydoc_usergroups_users'      => '#__easydoc_usergroups_users_ibfk_2',
            '#__easydoc_categories_usergroups' => '#__easydoc_categories_usergroups_ibfk_2'
        ];

        $queries = [];

        foreach ($old_constraints as $table => $name)
        {
            if ($this->_constraintExists($table, $name)) {
                $queries[] = sprintf('ALTER TABLE %s DROP FOREIGN KEY %s', $table, $name);
            }
        }

        if ($this->_tableExists('#__easydoc_categories_usergroups')) {
            $queries[] = 'ALTER TABLE #__easydoc_categories_usergroups MODIFY easydoc_usergroup_id bigint(20) NOT NULL';
        }

        if (!empty($queries)) {
            $this->_executeQueries($queries);
        }
    }

    protected function _splitSql($sql)
    {
		$start     = 0;
		$open      = false;
		$comment   = false;
		$endString = '';
		$end       = strlen($sql);
		$queries   = [];
		$query     = '';

        for ($i = 0; $i < $end; $i++)
        {
			$current      = substr($sql, $i, 1);
			$current2     = substr($sql, $i, 2);
			$current3     = substr($sql, $i, 3);
			$lenEndString = strlen($endString);
			$testEnd      = substr($sql, $i, $lenEndString);

            if ($current == '"' || $current == "'" || $current2 == '--'
                || ($current2 == '/*' && $current3 != '/*!' && $current3 != '/*+')
                || ($current == '#' && $current3 != '#__')
                || ($comment && $testEnd == $endString))
            {
                // Check if quoted with previous backslash
                $n = 2;

                while (substr($sql, $i - $n + 1, 1) == '\\' && $n < $i) {
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

							$open      = false;
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
                        else $endString = $current;

                        if ($comment && $start < $i) {
                            $query = $query . substr($sql, $start, ($i - $start));
                        }
                    }
                }
            }

            if ($comment) {
                $start = $i + 1;
            }

            if (($current == ';' && !$open) || $i == $end - 1)
            {
                if ($start <= $i) {
                    $query = $query . substr($sql, $start, ($i - $start + 1));
                }

				$query = trim($query);

                if ($query)
                {
                    if (($i == $end - 1) && ($current != ';')) {
                        $query = $query . ';';
                    }

                    $queries[] = $query;
                }

                $query = '';
                $start = $i + 1;
            }
        }

        return $queries;
    }
}
