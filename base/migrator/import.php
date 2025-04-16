<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Component\Migrator;
use EasyDocLabs\Library;
use EasyDocLabs\WP;

/**
 * EasyDocs Importer Class.
 */
class MigratorImport extends Migrator\MigratorImportAbstract
{
    /*protected function _actionImport_pages(Migrator\MigratorContext $context)
    {
        $getPageMeta = function ($page_id, $metadata) {
            $results = [];
            foreach ($metadata as $row) {
                if ($row->post_id === $page_id) {
                    $results[$row->meta_key] = $row->meta_value;
                }
            }

            return $results;
        };

        $driver = $this->getObject('lib:database.driver.mysqli');
        $page_query = $this->getObject('lib:database.query.select')->table('easydoc_pages_mig');
        $meta_query  = $this->getObject('lib:database.query.select')->table('easydoc_pages_meta_mig');

        $pages = $driver->select($page_query, Library\Database::FETCH_ARRAY_LIST);
        $metadata = $driver->select($meta_query, Library\Database::FETCH_OBJECT_LIST);

        foreach ($pages as $page)
		{
            $page['meta_input'] = $getPageMeta($page['ID'], $metadata);
            unset($page['ID']);
            unset($page['guid']);

			$id = \EasyDocLabs\WP::wp_insert_post($page);

            if ($id && ($page['post_type'] == 'page'))
			{
				if (strpos($page['post_content'], 'wp:easydoc/list') !== false || preg_match('/\[easydoc(?:\s+.*?|\s*)\]/', $page['post_content']))
				{
					// We got a page block, we need to set it as such

					$easydoc_pages = Base\BlockPage::getPages(true);

					$easydoc_pages[] = $id;

					Base\BlockPage::savePages($easydoc_pages);
				}
			}
        }

        WP::flushRewriteRules(true);
    }*/

    protected function _actionCheck(Migrator\MigratorContext $context)
    {
        $job = $context->getJob();
        $job->append($this->getConfig());

        $translator = $this->getObject('translator');

        $source = substr($this->getConfig()->source_version, 0, 3);
        $current = substr(Version::VERSION, 0, 3);

        $metadata = $this->getConfig()->metadata;

        if (!$metadata->joomla) {
            if (version_compare($source, $current, '<'))
            {
                $context->setError($translator->translate(
                    'The exported data is from EasyDocs version {source}. Please first upgrade the source installation to EasyDocs {current} and then export the data again to import it here.'
                    , array('source' => $source, 'current' => $current)
                ));

                return false;
            }

            if (version_compare($source, $current, '>'))
            {
                $context->setError($translator->translate(
                    'The exported data is from a newer EasyDocs version. Please first upgrade this installation to EasyDocs {source} to import this file.',
                    array('source' => $source, 'current' => $current)
                ));

                return false;
            }

            $categoryCount = $this->getObject('com://admin/easydoc.model.categories')->count();
            $documentCount = $this->getObject('com://admin/easydoc.model.documents')->count();

            if (!$this->getObject('request')->getQuery()->has('override')
                && ($categoryCount > 1 || $documentCount)
            ) {
                $context->setError($translator->translate(
                    'You need to delete all existing categories and documents before you start the migration process'
                ));

                return false;
            }
        }

        return true;
    }

    protected function _actionClear_cache(Migrator\MigratorContext $context)
    {
        $result = true;

        try {
            $this->getObject('com:easydoc.model.entity.config')->clearCache();
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }

	protected function _actionSync_users(Migrator\MigratorContext $context)
    {
		$result = true;

		try {
            $this->getObject('com:easydoc.controller.user')->sync(['reset' => true]);
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
	}

    /**
     * Remove internal group states making them all external.
     *
     * This is done by default for avoiding depending on custom WP roles migration from the old site to the new one.
     *
     * @param Migrator\MigratorContext $context
     * @return bool
     */
    protected function _actionHandle_internal_usergroups(Migrator\MigratorContext $context)
    {
        $result = true;

        try {
            $ignore = ['administrator', 'editor', 'author', 'contributor', 'subscriber'];

            $query = $this->getObject('lib:database.query.update')
                          ->table('easydoc_usergroups')
                          ->values('internal = :value')
                          ->where('internal = :current')
                          ->where('name NOT IN :ignore')
                          ->bind(['value' => 0, 'current' => 1, 'ignore' => $ignore]);

            $this->getObject('lib:database.driver.mysqli')->update($query);
        }
        catch (\Exception $e)
        {
            $result = false;
        }

        return $result;
    }

    protected function _addJoomlaJobs(Library\ObjectConfig $config)
    {
        $source = substr($config->source_version, 0, 3);

        if (version_compare($source, '2.0', '>='))
        {
            $config->jobs->append(array(
                'check' => array(
                    'action' => 'check',
                    'label'     => 'Checking your system',
                ),
                'insert_documents'          => array(
                    'action'    => 'insert',
                    'chunkable' => true,
                    'label'     => 'Inserting documents',
                    'source'    => 'easydoc_documents',
                    'table'     => 'easydoc_documents_mig',
                    'create_from'    => 'easydoc_documents'
                ),
                'insert_categories'         => array(
                    'action'    => 'insert',
                    'chunkable' => true,
                    'label'     => 'Inserting categories',
                    'source'    => 'easydoc_categories',
                    'table'     => 'easydoc_categories_mig',
                    'create_from'    => 'easydoc_categories'
                ),
                'insert_category_relations' => array(
                    'action'    => 'insert',
                    'chunkable' => true,
                    'label'     => 'Inserting category relations',
                    'source'    => 'easydoc_category_relations',
                    'table'     => 'easydoc_category_relations_mig',
                    'create_from'    => 'easydoc_category_relations'
                ),
                'insert_category_orderings' => array(
                    'action'    => 'insert',
                    'chunkable' => true,
                    'label'     => 'Inserting category orderings',
                    'source'    => 'easydoc_category_orderings',
                    'table'     => 'easydoc_category_orderings_mig',
                    'create_from'    => 'easydoc_category_orderings'
                ),
                'insert_containers'          => array(
                    'action'    => 'insert',
                    'chunkable' => true,
                    'label'     => 'Inserting containers',
                    'source'    => 'files_containers',
                    'table'     => 'easydoc_containers_mig',
                    'create_from'    => 'files_containers',
                ),
                'move_documents'            => array(
                    'action' => 'move',
                    'label'  => 'Moving Documents',
                    'source' => 'easydoc_documents_mig',
                    'target' => 'easydoc_documents'
                ),
                'move_categories'           => array(
                    'action' => 'move',
                    'label'  => 'Moving categories',
                    'source' => 'easydoc_categories_mig',
                    'target' => 'easydoc_categories'
                ),
                'move_category_relations'   => array(
                    'action' => 'move',
                    'label'  => 'Moving category relations',
                    'source' => 'easydoc_category_relations_mig',
                    'target' => 'easydoc_category_relations'
                ),
                'move_category_orderings'   => array(
                    'action' => 'move',
                    'label'  => 'Moving category orderings',
                    'source' => 'easydoc_category_orderings_mig',
                    'target' => 'easydoc_category_orderings'
                ),
                'fix_container_paths' => array(
                    'action' => 'query',
                    'query'  => "
                        UPDATE `#__easydoc_containers_mig` SET path = REPLACE(path, 'ait_themes-files/', 'wp-content/easydoclabs/');
                        UPDATE `#__easydoc_containers_mig` SET path = 'wp-content/easydoclabs/easydoc-files' WHERE slug = 'easydoc-files';
                        ",
                    'label'  => 'Fixing container paths'
                ),
                'import_containers' => array(
                    'action' => 'import_containers', // forwarded to copy if not multi-site
                    'source' => 'easydoc_containers_mig',
                    'target' => 'files_containers',
                    'label'  => 'Importing containers',
                    'operation' => 'REPLACE'
                ),
                'cleanup'            => array(
                    'action' => 'query',
                    'query'  => "
                        DROP TABLE IF EXISTS `#__easydoc_containers_mig`;
                        DROP TABLE IF EXISTS `#__easydoc_modules_mig`;
                        DROP TABLE IF EXISTS `#__easydoc_menu_mig`;
                        ",
                    'label'  => 'Cleaning up'
                ),
                'clear_cache' => [
                    'action' => 'clear_cache',
                    'label'  => 'Clearing cache',
                ],
            ));
        }

        if (version_compare($source, '3.0', '>='))
        {
            $config->jobs->append(array(
                'insert_category_folders' => array(
                    'action' => 'insert',
                    'label'  => 'Inserting category folders',
                    'source' => 'easydoc_category_folders',
                    'table'  => 'easydoc_category_folders_mig',
                    'after'  => 'insert_category_orderings',
                    'create_from' => 'easydoc_category_folders'
                ),
                'insert_tags' => array(
                    'action' => 'insert',
                    'label'  => 'Inserting tags',
                    'source' => 'easydoc_tags',
                    'table'  => 'easydoc_tags_mig',
                    'after'  => 'insert_category_folders',
                    'create_from' => 'easydoc_tags'
                ),
                'insert_tags_relations' => array(
                    'action' => 'insert',
                    'label'  => 'Inserting tag relations',
                    'source' => 'easydoc_tags_relations',
                    'table'  => 'easydoc_tags_relations_mig',
                    'after'  => 'insert_tags',
                    'create_from' => 'easydoc_tags_relations'
                ),
                'move_category_folders'   => array(
                    'action' => 'move',
                    'label'  => 'Moving category folders',
                    'source' => 'easydoc_category_folders_mig',
                    'target' => 'easydoc_category_folders',
                    'after'  => 'move_category_orderings'
                ),
                'move_tags'   => array(
                    'action' => 'move',
                    'label'  => 'Moving tags',
                    'source' => 'easydoc_tags_mig',
                    'target' => 'easydoc_tags',
                    'after'  => 'move_category_folders'
                ),
                'move_tags_relations'   => array(
                    'action' => 'move',
                    'label'  => 'Moving tag relations',
                    'source' => 'easydoc_tags_relations_mig',
                    'target' => 'easydoc_tags_relations',
                    'after'  => 'move_tags'
                )
            ));
        }

        // It was added to export in 3.0.2
        if (version_compare($config->source_version, '3.0.2', '>=')) {
            $config->jobs->append([
                'insert_document_contents'          => array(
                    'action'    => 'insert',
                    'chunkable' => true,
                    'label'     => 'Inserting document contents',
                    'source'    => 'easydoc_document_contents',
                    'table'     => 'easydoc_document_contents_mig',
                    'create_from'    => 'easydoc_document_contents',
                    'after'  => 'insert_tags_relations',
                ),
                'move_document_contents' => [
                    'action' => 'move',
                    'label'  => 'Moving document contents',
                    'source' => 'easydoc_document_contents_mig',
                    'target' => 'easydoc_document_contents',
                    'after'  => 'move_tags_relations'
                ]
            ]);
        }

    }

    protected function _addWordpressJobs(Library\ObjectConfig $config)
    {
        $config->jobs->append(array(
            'check'                            => array(
                'action' => 'check',
                'label'  => 'Checking your system',
            ),
            'insert_categories'                => array(
                'action'      => 'insert',
                'chunkable'   => true,
                'label'       => 'Inserting categories',
                'source'      => 'easydoc_categories',
                'table'       => 'easydoc_categories_mig',
                'create_from' => 'easydoc_categories'
            ),
            'insert_category_group_access' => array(
                'action'      => 'insert',
                'chunkable'   => true,
                'label'       => 'Inserting category group access data',
                'source'      => 'easydoc_category_group_access',
                'table'       => 'easydoc_category_group_access_mig',
                'create_from' => 'easydoc_category_group_access'
            ),
            'insert_document_group_access' => array(
                'action'      => 'insert',
                'chunkable'   => true,
                'label'       => 'Inserting document group access data',
                'source'      => 'easydoc_document_group_access',
                'table'       => 'easydoc_document_group_access_mig',
                'create_from' => 'easydoc_document_group_access'
            ),
            'insert_category_relations'        => array(
                'action'      => 'insert',
                'chunkable'   => true,
                'label'       => 'Inserting category relations',
                'source'      => 'easydoc_category_relations',
                'table'       => 'easydoc_category_relations_mig',
                'create_from' => 'easydoc_category_relations'
            ),
            'insert_category_orderings'        => array(
                'action'      => 'insert',
                'chunkable'   => true,
                'label'       => 'Inserting category orderings',
                'source'      => 'easydoc_category_orderings',
                'table'       => 'easydoc_category_orderings_mig',
                'create_from' => 'easydoc_category_orderings'
            ),
            'insert_category_folders'          => array(
                'action'      => 'insert',
                'label'       => 'Inserting category folders',
                'source'      => 'easydoc_category_folders',
                'table'       => 'easydoc_category_folders_mig',
                'create_from' => 'easydoc_category_folders'
            ),
            'insert_containers'                => array(
                'action'      => 'insert',
                'chunkable'   => true,
                'label'       => 'Inserting containers',
                'source'      => 'files_containers',
                'table'       => 'easydoc_containers_mig',
                'create_from' => 'files_containers',
            ),
            'insert_settings'                  => array(
                'action'      => 'insert',
                'chunkable'   => true,
                'label'       => 'Inserting settings',
                'source'      => 'options',
                'table'       => 'easydoc_options_mig',
                'create_from' => 'options',
            ),
            'insert_documents'                 => array(
                'action'      => 'insert',
                'chunkable'   => true,
                'label'       => 'Inserting documents',
                'source'      => 'easydoc_documents',
                'table'       => 'easydoc_documents_mig',
                'create_from' => 'easydoc_documents'
            ),
            'insert_document_contents'         => array(
                'action'      => 'insert',
                'chunkable'   => true,
                'label'       => 'Inserting document contents',
                'source'      => 'easydoc_document_contents',
                'table'       => 'easydoc_document_contents_mig',
                'create_from' => 'easydoc_document_contents',
            ),
            'insert_permissions'               => array(
                'action'      => 'insert',
                'label'       => 'Inserting permissions',
                'source'      => 'easydoc_permissions',
                'table'       => 'easydoc_permissions_mig',
                'create_from' => 'easydoc_permissions'
            ),
            /*'insert_pages'                     => array(
                'action'      => 'insert',
                'label'       => 'Inserting pages',
                'source'      => 'posts',
                'table'       => 'easydoc_pages_mig',
                'create_from' => 'posts'
            ),
            'insert_pages_meta'                => array(
                'action'      => 'insert',
                'label'       => 'Inserting page metadata',
                'source'      => 'postmeta',
                'table'       => 'easydoc_pages_meta_mig',
                'create_from' => 'postmeta'
            ),*/
            'insert_scans'                     => array(
                'action'      => 'insert',
                'label'       => 'Inserting scans',
                'source'      => 'easydoc_scans',
                'table'       => 'easydoc_scans_mig',
                'create_from' => 'easydoc_scans'
            ),
            'insert_tags'                      => array(
                'action'      => 'insert',
                'label'       => 'Inserting tags',
                'source'      => 'easydoc_tags',
                'table'       => 'easydoc_tags_mig',
                'create_from' => 'easydoc_tags'
            ),
            'insert_tags_relations'            => array(
                'action'      => 'insert',
                'label'       => 'Inserting tag relations',
                'source'      => 'easydoc_tags_relations',
                'table'       => 'easydoc_tags_relations_mig',
                'create_from' => 'easydoc_tags_relations'
            ),
            'insert_usergroups'                => array(
                'action'      => 'insert',
                'label'       => 'Inserting usergroups',
                'source'      => 'easydoc_usergroups',
                'table'       => 'easydoc_usergroups_mig',
                'create_from' => 'easydoc_usergroups'
            ),
            'insert_usergroups_users'          => array(
                'action'      => 'insert',
                'label'       => 'Inserting usergroup users',
                'source'      => 'easydoc_usergroups_users',
                'table'       => 'easydoc_usergroups_users_mig',
                'create_from' => 'easydoc_usergroups_users'
            ),
            'move_categories'                  => array(
                'action' => 'move',
                'label'  => 'Moving categories',
                'source' => 'easydoc_categories_mig',
                'target' => 'easydoc_categories'
            ),
            'move_category_group_access'   => array(
                'action' => 'move',
                'label'  => 'Moving category group access data',
                'source' => 'easydoc_category_group_access_mig',
                'target' => 'easydoc_category_group_access'
            ),
            'move_document_group_access'   => array(
                'action' => 'move',
                'label'  => 'Moving document group access data',
                'source' => 'easydoc_document_group_access_mig',
                'target' => 'easydoc_document_group_access'
            ),
            'move_category_folders'            => array(
                'action' => 'move',
                'label'  => 'Moving category folders',
                'source' => 'easydoc_category_folders_mig',
                'target' => 'easydoc_category_folders',
            ),
            'move_category_relations'          => array(
                'action' => 'move',
                'label'  => 'Moving category relations',
                'source' => 'easydoc_category_relations_mig',
                'target' => 'easydoc_category_relations'
            ),
            'move_category_orderings'          => array(
                'action' => 'move',
                'label'  => 'Moving category orderings',
                'source' => 'easydoc_category_orderings_mig',
                'target' => 'easydoc_category_orderings'
            ),
            'move_documents'                   => array(
                'action' => 'move',
                'label'  => 'Moving documents',
                'source' => 'easydoc_documents_mig',
                'target' => 'easydoc_documents'
            ),
            'move_document_contents'           => [
                'action' => 'move',
                'label'  => 'Moving document contents',
                'source' => 'easydoc_document_contents_mig',
                'target' => 'easydoc_document_contents',
            ],
            'move_permissions'                 => array(
                'action' => 'move',
                'label'  => 'Moving permissions',
                'source' => 'easydoc_permissions_mig',
                'target' => 'easydoc_permissions'
            ),
            'move_scans'                       => array(
                'action' => 'move',
                'label'  => 'Moving scans',
                'source' => 'easydoc_scans_mig',
                'target' => 'easydoc_scans'
            ),
            'move_tags'                        => array(
                'action' => 'move',
                'label'  => 'Moving tags',
                'source' => 'easydoc_tags_mig',
                'target' => 'easydoc_tags',
            ),
            'move_tags_relations'              => array(
                'action' => 'move',
                'label'  => 'Moving tag relations',
                'source' => 'easydoc_tags_relations_mig',
                'target' => 'easydoc_tags_relations',
            ),
            'move_usergroups'                  => array(
                'action' => 'move',
                'label'  => 'Moving usergroups',
                'source' => 'easydoc_usergroups_mig',
                'target' => 'easydoc_usergroups',
            ),
            'handle_internal_usergroups'       => array(
                'action' => 'handle_internal_usergroups',
                'label'  => 'handling_internal_usergroups'
            ),
            'move_usergroups_users'            => array(
                'action' => 'move',
                'label'  => 'Moving usergroups users',
                'source' => 'easydoc_usergroups_users_mig',
                'target' => 'easydoc_usergroups_users',
            ),
            'import_containers'                => array(
                'action'    => 'import_containers', // forwarded to copy if not multi-site
                'source'    => 'easydoc_containers_mig',
                'target'    => 'files_containers',
                'label'     => 'Importing containers',
                'operation' => 'REPLACE'
            ),
            'import_settings'                  => array(
                'action'           => 'copy',
                'source'           => 'easydoc_options_mig',
                'target'           => 'options',
                'label'            => 'Importing settings',
                'operation'        => 'REPLACE',
                'skip_primary_key' => true
            ),
            /*'import_pages'                     => [
                'action' => 'import_pages',
                'label'  => 'Importing pages',
            ],*/
            'cleanup'                          => array(
                'action' => 'query',
                'query'  => "
                    DROP TABLE IF EXISTS `#__easydoc_containers_mig`;
                    DROP TABLE IF EXISTS `#__easydoc_options_mig`;
                    DROP TABLE IF EXISTS `#__easydoc_pages_mig`;
                    DROP TABLE IF EXISTS `#__easydoc_pages_meta_mig`;
					UPDATE `#__easydoc_users` SET `permissions_map` = NULL;
                    ",
                'label'  => 'Cleaning up'
            ),
            'clear_cache'                      => [
                'action' => 'clear_cache',
                'label'  => 'Clearing cache',
			],
			'sync_users'                       => [
                'action' => 'sync_users',
                'label'  => 'Syncing users',
			]
        ));

    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $app    = $config->metadata->joomla ? 'joomla' : 'wordpress';
        $source = substr($config->source_version, 0, 3);

        $config->append(array(
            'label'     =>  'EasyDocs',
            'extension' => 'easydoc',
            'jobs'      => array()
        ));

        if ($app === 'joomla') {
            $this->_addJoomlaJobs($config);
        } else {
            $this->_addWordpressJobs($config);
        }

        parent::_initialize($config);
    }

    protected function _actionImport_containers(Migrator\MigratorContext $context)
    {   
        $result = 'skipped';

        // Only import containers if site is not a multi-site

        if (!WP::is_multisite()) {
            $result = $this->execute('copy', $context); // Forward action
        }

        return $result;
    }
}
