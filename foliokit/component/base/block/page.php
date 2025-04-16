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
 * Renders response contents in the page by replacing the block/shortcode
 *
 * @author  Ercan Ozkaya <http://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Base
 */
class BlockPage extends BlockAbstract
{
	protected $_rendered = false;

	protected $_events_registered = false;

	protected static $_pages = [];

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'post_types' => ['page']
        ]);
        parent::_initialize($config);
    }

    public function getSupports()
    {
        $supports = parent::getSupports();

        $supports['multiple'] = false;

        return $supports;
    }

    public function beforeRegister()
    {
        parent::beforeRegister();

        if (!$this->_events_registered)
		{
            WP::add_action('init', function()
			{
                $pages = static::getPages(false, ['block' => $this->getBlockName()]);

				foreach ($pages as $page)
				{
					// Handle frontend pages

					if ($page->permalink == '' && WP::get_option('show_on_front') === 'page' && WP::get_option('page_on_front') === $page->ID)
					{
						$router = $this->getObject(sprintf('com://site/%s.dispatcher.router', $this->getIdentifier()->getPackage()), ['request' => $this->getObject('request')]);

					    /**
                         * Check if Wordpress default rule for pages was triggered
                         * If so, check if page really exists
                         * If not, we are on a subfolder for the homepage
                         * Intercept and set the real page as the pagename and the rest of the query as route for EasyDoc
                         */
                        WP::add_action('parse_request', function ($wp_query) use($page, $router)
						{
                            $query = $wp_query->query_vars;

							if (WP::get_option('permalink_structure'))
							{
								// SEF routes (WP rewrite rules apply)

								$is_attachment_rule = isset($query['attachment']);
								$is_page_rule = isset($query['pagename']) && isset($query['page']) && $query['page'] === '';
								$is_post_rule = isset($query['name']) && isset($query['page']) && $query['page'] === '';
								$is_404 = isset($query['error']) && $query['error'] = '404';

								if ($is_attachment_rule || $is_post_rule || $is_page_rule || $is_404)
								{
									$remove = []; // Query variables to be removed from current query

									if ($is_attachment_rule)
									{
										// We get here when the WP attachment rewrite rule kicks in. We can safely assume that this is NOT an
										// attachment request given the page context we are in (we have verified that the home theme settings are set
										// to show a page and that a foliokit page is set)
	
										$route = $wp_query->request;
										
										$remove[] = 'attachment';
									}
									elseif ($is_404)
									{
										// Depending on the permalink settings, WordPress may not match a rewrite rule ...
										// Example: Setting permalinks to "Post name", enables $wp_rewrite->use_verbose_page_rules
										// which will invalidate the catch all re-write rule '([^/]+)(?:/([0-9]+))?/?$' => 'index.php?name=$matches[1]&page=$matches[2]'
										// when a page is not found for the selected path, this prevents EasyDocs from routing the request properly.
										// We then catch the 404 and attempt to route it as we know nothing matched and that the current page being handled is
										// set as frontpage

										$route = $wp_query->request;

										$remove[] = 'error';
									}
									else
									{
										if ($is_page_rule) {
											$route = $query['pagename'];
										} else {
											$route = $query['name'];
										}

										$remove = ['page', 'name'];
									}

									//if ($page->blocks)

									// Only take over the request if the route resolves, meaning that it exists and it's handled by the router

									if ($router->resolve($route, $page->attributes))
									{
										foreach ($remove as $var) {
											unset($query[$var]);
										}

										$query['route']    = $route;
										$query['pagename'] = $page->post_name;
									}
								}
							}
							else
							{
								// Non SEF routes handling

								// Only take over the request if the route resolves, meaning that it exists and it's handled by the router

								if (isset($query['route']))
								{
									$route = $query['route'];

									if ($router->resolve($route, $page->attributes)) {
										$query['pagename'] = $page->post_name;
									}
								}
							}

                            $wp_query->query_vars = $query;
                        });

                        // Block automatic redirection from any subpage of the frontpage to site root

                        WP::add_filter('redirect_canonical', function($redirect_url)
						{
                            if (WP::is_page() && WP::get_option('show_on_front') === 'page' &&
                                WP::get_queried_object_id() === (int) WP::get_option('page_on_front')
                            ) {
                                $redirect_url = false;
                            }

                            return $redirect_url;
                        });
					}
				}

                WP::add_filter('query_vars', function($query_vars) {
                    $query_vars[] = 'route';
                    return $query_vars;
                });
            });

            // If GET[name] or POST[name] is set, Wordpress uses it for routing
            WP::add_action( 'parse_request', function($wp_query) {
                if (isset($wp_query->query_vars['pagename'])) {
                    $page = WP::get_page_by_path($wp_query->query_vars['pagename']);
                    $pages = static::getPages(true);

                    if ($page && in_array($page->ID, $pages)) {
                        unset($wp_query->query_vars['name']);
                    }
                }

            });

			/*WP::add_filter( 'wp_insert_post_data', function($data, $postarr)
			{
				$content = $data['post_content'];

				$registry = $this->getObject('com:base.block.registry');

				$has_page_block = false;

				foreach ($registry->getPageBlocks() as $block) {
					$has_page_block = WP::has_block($block->getBlockName(), $postarr['ID']); // Search for Gutenberg page blocks in content
				}

				$pattern = WP::get_shortcode_regex();

				$invalidate_shortcode = function($shortcode, $name, $content) use ($postarr)
				{
					$translator = $this->getObject('translator');

					$message = $postarr['post_type'] == 'page' ? $translator->translate('Only one page block is allowed per page') : $translator->translate('Page blocks cannot be added on posts');

					$invalidated = str_replace(sprintf('[%s', $name), sprintf('[%s-INVALID !!!%s!!!', $name, $message), $shortcode);

					return str_replace($shortcode, $invalidated, $content);
				};

				preg_match_all( "/$pattern/s", $content, $matches, PREG_SET_ORDER);

				foreach ($matches as $match)
				{
					if ($registry->hasShortcode($match[2]))
					{
						$block = $registry->getBlockByShortcode($match[2]);

						if ($block instanceof BlockPage)
						{
							// Only allow one page block per page, page blocks can only be added on pages

							if ($has_page_block || $postarr['post_type'] != 'page') {
								$content = $invalidate_shortcode($match[0], $match[2], $content);
							}
						}
					}
				}

				$data['post_content'] = $content;

				return $data;

			}, 99, 2);*/

            $deletePost = function($id)
			{
                $pages = static::getPages(true);

                if (in_array($id, $pages)) {
                    $key = array_search($id, $pages);

                    unset($pages[$key]);

                    static::savePages($pages);
                }
            };

            WP::add_action('save_post', function($id, $post = null, $update = false) use($deletePost)
			{
                if ($post->post_status === 'trash')
				{
                    $deletePost($id);
                }
				elseif($update)
				{

					/** @var $registry BlockRegistry */
                    $registry = $this->getObject('com:base.block.registry');

					// Remove the page from the easydoc_pages option if it's not a page block
					$pages = get_option('easydoc_pages', []);

					if (in_array($post->ID, $pages))
					{
						// Always remove the page from the list when saving, it will get added back if it's still there

						$pages = array_diff($pages, [$post->ID]);
						static::savePages($pages);
					}

                    $savePage = function($blockname, $attributes, $post) use($registry)
					{
                        $block = $registry->getBlock($blockname);

                        // Parse the attributes from the block
                        if (is_string($attributes)) {
                            $attributes = WP::shortcode_parse_atts($attributes);
                        }

                        $context = new Library\ObjectConfig([
                            'attributes' => !empty($attributes) ? $attributes : []
                        ]);

                        $context->post = $post;
                        $context->post->permalink = str_replace(WP::get_home_url(), '', WP::get_permalink($post->ID));

                        if ($block->isSupported($context)) {
                            $block->beforeSave($context);
                        }
                    };

					$content = $post->post_content;

					$query = $this->getObject('lib:database.query.select')->table('postmeta')
								->columns('meta_value')->where('post_id = :id')->where('meta_key = :key')->bind(['id' => $id, 'key' => '_elementor_data']);

					$post_data = $this->getObject('lib:database.driver.mysqli')->select($query, Library\Database::FETCH_FIELD);

					if ($post_data)
					{
						// Elementor data found for this page, append it to the content for parsing

						$content = $post->post_content . $post_data;
					}

					$process_shortcode = function ($content) use ($registry, $savePage, $post)
					{
						$pattern = WP::get_shortcode_regex($registry->getShortcodes());

						preg_match_all( "/$pattern/s", $content, $matches, PREG_SET_ORDER);

						foreach ($matches as $match)
						{
							if ($registry->hasShortcode($match[2]))
							{
								$block = $registry->getBlockByShortcode($match[2]);
								$savePage($block->getBlockName(), $match[3], $post);
							}
						}
					};

					$process_shortcode($content);

                    if (class_exists('\EasyDocLabs\WP\Block_Parser'))
					{
                        $parser = new WP\Block_Parser();
                        $parser->parse($post->post_content);

						if (!empty($parser->output))
						{
							$iterate = function($blocks) use ($registry, $savePage, $post, &$iterate)
							{
								foreach ($blocks as $block)
								{
									if (empty($block['innerBlocks']))
									{
										if ($registry->hasBlock($block['blockName'])) {
											$savePage($block['blockName'], $block['attrs'], $post);
										}
									}
									else $iterate($block['innerBlocks']);
								}
							};

							$iterate($parser->output);
						}
                    }

					if (class_exists('ACF'))
					{
						// Advanced Custom Fields integration

						if ($fields = get_field_objects($id, false))
						{

							$fields_handler = function($fields) use ($process_shortcode, $post, &$fields_handler)
							{
								foreach ($fields as $field)
								{
									if (have_rows($field['name'], $post->id))
									{
										while (have_rows($field['name']))
										{
											$row = (array) the_row();

											foreach ($row as $key => $value) {
												if ($sub_field = get_sub_field_object($key, false)) {
													$fields_handler([$sub_field]);
												}
											}
										}			
									}
									else
									{
										if (in_array($field['type'], ['text', 'textarea', 'wysiwyg'])) {
											$process_shortcode($field['value']);
										}
									}				
								}
							};

							$fields_handler($fields);
						}
					}
                }
            }, 10, 3);

            WP::add_action('delete_post', $deletePost);

            $this->_events_registered = true;
        }
    }

    public static function savePages($pages)
    {
        $pages = array_unique($pages);

        WP::update_option('easydoc_pages', $pages, true);

		self::$_pages = []; // Reset pages cache

        WP::flushRewriteRules();
    }

    /**
     * @return array
     */
    public static function getPages($ids = false, $filters = [])
    {
		asort($filters);

		$signature = base64_encode(serialize($filters));

		if (!isset(self::$_pages[$signature]))
		{
			$easydoc_pages = WP::get_option('easydoc_pages', []);
			$pages          = [];
	
			if (!empty($easydoc_pages)) 
			{
				$query = \Foliokit::getObject('lib:database.query.select')
				->table('posts')
				->columns(['ID', 'post_name', 'post_content'])
				->where('ID IN :id')
				->where('post_type = :type')
				->bind(['id' => (array) $easydoc_pages, 'type' => 'page']);
	
				$posts = \Foliokit::getObject('lib:database.driver.mysqli')->select($query, Library\Database::FETCH_OBJECT_LIST);
	
				$site_folder = \Foliokit::getObject('request')->getSiteUrl()->toString(Library\HttpUrlInterface::PATH);

				foreach ($posts as $post)
				{
					$url = \Foliokit::getObject('lib:http.url', ['url' => WP::get_permalink($post->ID)]);

					$permalink = $url->toString(Library\HttpUrlInterface::PATH | Library\HttpUrlInterface::QUERY | Library\HttpUrlInterface::FRAGMENT);

					if ($site_folder && strpos($permalink, $site_folder) === 0) {
						$permalink = substr($permalink, strlen($site_folder));
					}

					$permalink = trim($permalink, '/');

					if (strpos($permalink, 'index.php') === 0)
					{
						$permalink = substr($permalink, 10);
						$file = 'index.php';
					}
	
					if ($permalink)
					{
						$regexp = '(' . preg_quote($permalink) . ')/(.*?)/?$';
					
						if (isset($file)) {
							$regexp = $file . '/' . $regexp;
						}

						$post->rewrite_rule = [$regexp => 'index.php?pagename=$matches[1]&route=$matches[2]'];
					}
	
					$post->permalink = $permalink;
					
					if ($filters)
					{
						$filter_blocks = function($blocks) use ($filters, &$filter_blocks)
						{
							$result = false;

							foreach ($blocks as $block)
							{
								if (isset($filters['block'])) {
									if ($block['blockName'] == $filters['block']) return $block;
								}
								
								if (!empty($block['innerBlocks'])) $result = $filter_blocks($block['innerBlocks']);

								if ($result) break;
							}

							return $result;
						};

						$block = $filter_blocks(parse_blocks($post->post_content));

						if ($block === false) continue; // Do not include the page

						$post->attributes = $block['attrs'];
					}

					unset($post->post_content);
	
					$pages[$post->ID] = $post;
				}

				self::$_pages[$signature] = $pages;
			}
		}
		else $pages = self::$_pages[$signature];

	    return $ids === true ? array_keys($pages) : $pages;
    }

    public function beforeSave($context)
    {
        if($context->post->post_type === 'page')
        {
			$pages   = static::getPages(true);
			$pages[] = $context->post->ID;

            static::savePages($pages);
        }
    }

    public function beforeRender($context)
    {
        if (!$context->query->component) {
            $context->query->component = $this->getIdentifier()->getPackage();
        }
    }

    public function render($context)
    {
		$output = '';

		$request = $this->getObject('request');

        $context->append(['query' => [], 'attributes' => []]);

		if (!$context->generic)
		{
			foreach ($this->getAttributes() as $name => $settings)
			{
				if ($context->attributes->$name === null && isset($settings['default'])) {
					$context->attributes->$name = $settings['default'];
				}

				// Add request options values to the query
				if (isset($settings['request'])) {
					$context->query->append([$settings['request'] => $context->attributes->{$name}]);
				}
			}

			// REST_REQUEST is true for Gutenberg editor's render requests
			if (!WP::is_admin() && (!defined('REST_REQUEST') || !REST_REQUEST))
			{
				if ($this->beforeRender($context) !== false)
				{
					$post = WP::get_post();

					if (isset($post) && $post->post_type == 'post')
					{
						$context->merge([
							'generic' => true,
							'message' => ['title' => 'Block misuse', 'description' => 'This block cannot be included on posts, it can only be included on pages', 'type' => 'danger']
						]);
					}
					elseif (!$this->_rendered)
					{
						$request->getQuery()->add($context->query->toArray());

						$router = $this->getObject(sprintf('com://site/%s.dispatcher.router', $this->getIdentifier()->getPackage()), ['request' => $request]);

						foreach ($router->getResolvers() as $resolver) {
							$resolver->setDefaults($context->query->toArray());
						}

						$dispatcher = $this->getObject('com:base.dispatcher.page');

						try
						{
							$dispatcher->dispatch(['options' => $context->attributes, 'block' => $this->getIdentifier()]);

							$output = $dispatcher->render();
	
							$this->_rendered = true; // Set block as rendered (Only one can render per page)
						}
						catch (Library\ControllerExceptionFormatNotSupported $exception)
						{
							$context->merge([
								'generic' => true,
								'message' => ['title' => 'Format not supported', 'description' => 'This block cannot be rendered, unsopported format: ' . $request->getFormat(), 'type' => 'danger']
							]);
						}
					}
					else $context->merge(['generic' => true, 'message' => ['title'=> 'Block conflict', 'description' => 'This block can only be included once per page', 'type' => 'danger']]);
				}
			}
		}

		if ($context->generic) {
			$output = parent::render($context);
		}

        return $output;
    }
}