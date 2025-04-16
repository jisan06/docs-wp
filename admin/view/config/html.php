<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\EasyDoc;
use EasyDocLabs\Component\Base;
use EasyDocLabs\Component\Files;
use EasyDocLabs\Component\Scheduler;
use EasyDocLabs\Library;

class ViewConfigHtml extends ViewHtml
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        if ($config->layout && strpos($config->layout, 'debug') === 0) {
            $config->append([
                'decorator' => 'foliokit'
            ]);
        }

        parent::_initialize($config);
    }

    protected function _fetchData(Library\ViewContextTemplate $context)
    {
        $context->data->upload_max_filesize = Files\ModelEntityContainer::getServerUploadLimit();

        $context->data->filetypes = array(
            'archive'  => array('7z', 'ace', 'bz2', 'dmg', 'gz', 'rar', 'tgz', 'zip'),
            'document' => array('csv', 'doc', 'docx', 'html', 'key', 'keynote', 'odp', 'ods', 'odt', 'pages', 'pdf', 'pps', 'ppt', 'pptx', 'rtf', 'tex', 'txt', 'xls', 'xlsx', 'xml'),
            'image'    => array('bmp', 'exif', 'gif', 'ico', 'jpeg', 'jpg', 'png', 'psd', 'tif', 'tiff'),
            'audio'    => array('aac', 'aif', 'aiff', 'alac', 'amr', 'au', 'cdda', 'flac', 'm3u', 'm3u', 'm4a', 'm4a', 'm4p', 'mid', 'mp3', 'mp4', 'mpa', 'ogg', 'pac', 'ra', 'wav', 'wma'),
            'video'    => array('3gp', 'asf', 'avi', 'flv', 'm4v', 'mkv', 'mov', 'mp4', 'mpeg', 'mpg', 'ogg', 'rm', 'swf', 'vob', 'wmv')
        );

        $config = $this->getObject('com:easydoc.model.configs')->fetch();

        $context->data->connect_support = $config->connectAvailable();

        if (substr($this->getLayout(), 0, 5) === 'debug')
        {
            $easydoc_pages = Base\BlockPage::getPages();

            $pages = [];
            if (count($easydoc_pages)) {
                $query = new \EasyDocLabs\WP\Query(['post_type' => 'page', 'post__in' => array_keys($easydoc_pages)]);

                if ($query->posts) {
                    foreach ($query->posts as $post) {
                        $permalink = trim(str_replace(\EasyDocLabs\WP::get_home_url(), '', \EasyDocLabs\WP::get_permalink($post->ID)), '/');

                        $post->permalink = [
                            $permalink,
                            '('.preg_quote($permalink).')/(.*?)/?$',
                            'index.php?pagename=$matches[1]&route=$matches[2]',
                        ];
                        $post->link = home_url($permalink);

                        $pages[] = $post;
                    }
                }
            }

            $context->data->pages = $pages;

            $context->data->document_count = $this->getObject('com:easydoc.model.documents')->count();
            $context->data->category_count = $this->getObject('com:easydoc.model.categories')->count();
            $context->data->user_count     = $this->getObject('com:base.model.users')->count();
            $context->data->folder_count   = $this->getObject('com:easydoc.model.folders')->tree(true)->count();
            $context->data->file_count     = $this->getObject('com:easydoc.model.files')->tree(true)->count();
            $context->data->scan_count     = $this->getObject('com:easydoc.model.scans')->count();
            $context->data->tag_count      = $this->getObject('com:easydoc.model.tags')->count();

            $context->data->scheduler_log = null;
            $path                         = Scheduler\DispatcherBehaviorSchedulable::getLogPath();
            if (file_exists($path)) {
                $context->data->scheduler_log =  \EasyDocLabs\WP::getFilesystem()->get_contents($path);
            }

            $context->data->jobs = $this->getObject('com:scheduler.model.jobs')->fetch();

            $adapter = $this->getObject('database.driver.mysqli');

            $query = $this->getObject('database.query.select')
                ->table('scheduler_metadata')
                ->where('type = :type')->bind(['type' => 'metadata']);

            $context->data->scheduler_metadata = $adapter->select($query, Library\Database::FETCH_OBJECT);

            $query = $this->getObject('database.query.select')
                ->table('options')
                ->columns()
                ->where('option_name LIKE :easydoc OR option_name LIKE :folio')
                ->bind(['easydoc' => 'easydoc%', 'folio' => 'folio%']);

            $context->data->wp_options = $adapter->select($query, Library\Database::FETCH_OBJECT_LIST);

            try {
                $connect = $this->getObject('connect');
                $context->data->connect = [
                    'error' => false,
                    'route' => (string)$connect->getRoute(['task' => 'scanner-test']),
                    'token' => $connect->generateToken(),
                    'site' => $connect->getSite(),
                    'local' => $connect->isLocal(),
                    'supported' => $connect->isSupported(),
                ];
            } catch (\Exception $e) {
                $context->data->connect = [
                    'error' => $e->getMessage(),
                ];
            }
        }

        $license_init = $this->getObject('license');
        $context->data->license = $license_init->getLicense();
        try {
            if ($context->data->license) {
                $context->data->has_connect = true;
                $context->data->license_error = null;
//                $context->data->license_claims = $context->data->license->getToken() ? \EasyDocLabs\WP::wp_json_encode($context->data->license->getToken()->getClaims(), JSON_PRETTY_PRINT) : 'error';
            } else {
                $context->data->has_connect = false;
                $context->data->license_error = $license_init->getError();
//                $context->data->license_claims = 'error';
            }

        } catch (EasyDoc\LicenseException $e) {
            $context->data->license_error = $e->getMessage();
            $context->data->license_claims = 'error';
        }

        $locked_groups = [];
        $permissions   = $config->permissions;

        foreach ($permissions as $action => $groups)
        {
            if (strpos($action, 'view') !== 0) {
                $locked_groups = array_merge($locked_groups, $permissions[$action] ?? []);
            }
        }

        $locked_groups = array_unique($locked_groups);

        $permissions_actions = EasyDoc\ViewBehaviorPermissible::getActions();

		$permissions_actions['Admin'] = [
			'manage'    => [
				'label'   => 'Manage',
				'attribs' => ['fixed' => false]],
			'configure' => [
				'label'   => 'Configure',
				'attribs' => ['fixed' => false]
			]
		];

        $context->data->permissions_actions = $permissions_actions;
        $context->data->locked_groups       = $locked_groups;
        $context->data->permissions         = $config->permissions;
        $context->data->config              = $config;

        parent::_fetchData($context);
    }
}
