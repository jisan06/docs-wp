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

/**
 * EasyDocs Importer Class.
 */
class MigratorExport extends Migrator\MigratorExportAbstract
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(array(
                'label'     =>  'EasyDocs',
                'extension' => 'easydoc',
                'jobs'  => array(
                    'export_categories'         => array(
                        'label'      => 'Exporting categories',
                        'table'      => 'easydoc_categories'
                    ),
                    'export_category_group_access' => array(
                        'label'      => 'Exporting category group access data',
                        'table'      => 'easydoc_category_group_access'
                    ),
                    'export_document_group_access' => array(
                        'label'      => 'Exporting document group access data',
                        'table'      => 'easydoc_document_group_access'
                    ),
                    'export_category_folders' => array(
                        'label'      => 'Exporting category folders',
                        'table'      => 'easydoc_category_folders'
                    ),
                    'export_category_orderings' => array(
                        'label'      => 'Exporting category orderings',
                        'table'      => 'easydoc_category_orderings'
                    ),
                    'export_category_relations' => array(
                        'label'      => 'Exporting category relations',
                        'table'      => 'easydoc_category_relations'
                    ),
                    'export_documents'          => array(
                        'label'      => 'Exporting documents',
                        'table'      => 'easydoc_documents'
                    ),
                    'export_document_contents' => array(
                        'label'      => 'Exporting document contents',
                        'table'      => 'easydoc_document_contents'
                    ),
                    'export_permissions' => array(
                        'label'      => 'Exporting permissions',
                        'table'      => 'easydoc_permissions'
                    ),
                    'export_scans' => array(
                        'label'      => 'Exporting scans',
                        'table'      => 'easydoc_scans'
                    ),
                    'export_tags' => array(
                        'label'      => 'Exporting tags',
                        'table'      => 'easydoc_tags'
                    ),
                    'export_tags_relations' => array(
                        'label'      => 'Exporting tag relations',
                        'table'      => 'easydoc_tags_relations'
                    ),
                    'export_usergroups' => array(
                        'label'      => 'Exporting user groups',
                        'table'      => 'easydoc_usergroups'
                    ),
                    'export_usergroups_users' => array(
                        'label'      => 'Exporting user groups relations',
                        'table'      => 'easydoc_usergroups_users'
                    ),
                    'export_containers'         => array(
                        'label'      => 'Exporting containers',
                        'table'      => 'files_containers',
                        'callback'  => function($query) {
                            $query->where('slug IN :slugs')->bind(array(
                                'slugs' => array('easydoc-files', 'easydoc-icons', 'easydoc-images')
                            ));
                        }
                    ),
                    'export_options'             => array(
                        'label'      => 'Exporting options',
                        'table'      => 'options',
                        'callback'   => function($query) {
							$query
								->columns(['option_name', 'option_value', 'autoload'])
								->where('option_name LIKE :easydoc')
								->where('option_name NOT LIKE :easydoccache')
								->bind(array(
									'easydoc'      => 'easydoc%',
									'easydoccache' => 'easydoc_cache%'
								));
                        }
                    )/*,
                    'export_pages'             => array(
                        'label'      => 'Exporting pages',
                        'table'      => 'posts',
                        'callback'   => function($query) {
							$query
								->where('post_status <> :trashed AND post_type NOT IN :types AND (post_content LIKE :block OR post_content LIKE :shortcode)')
								->bind(array(
									'block'     => '%wp:easydoc%',
									'shortcode' => '%[easydoc%]%',
									'types'     => ['revision'],
									'trashed'   => 'trash'
								));
                        }
                    ),
                    'export_pages_metadata'             => array(
                        'label'      => 'Exporting page metadata',
                        'table'      => 'postmeta',
                        'callback'   => function($query) {
                            $subq = $this->getObject('database.query.select')
                                ->table('posts')->columns('ID')
                                ->where('post_type NOT IN :nottypes')
                                ->where('post_content LIKE :block')
                                ->bind(array(
                                    'block' => '%wp:easydoc%',
                                    'nottypes' => ['revision']
                                ));

                            $query
                                ->where('post_id IN :posts')
                                ->bind(array(
                                    'posts' => $subq
                                ));
                        }
                    )*/
                )
            )
        );

        parent::_initialize($config);
    }
}
