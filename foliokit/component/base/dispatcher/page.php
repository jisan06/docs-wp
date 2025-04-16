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
 * Page dispatcher
 *
 * This is the core of page rendering in Foliokit. This class is instantiated as a singleton in Wordpress init action.
 *
 *
 * There are a few different ways we render content:
 *
 * * Backend:
 * We look for "component" in query parameters.
 * - If there is a bootstrapped component with the query parameter value we dispatch it (in _actionDispatch)
 * - If the URL contains "page" query parameter, this means it was registered in _registerAdminPages method.
 * Response is then printed in _actionRender_admin
 * - If there is no "page" parameter and the request format is HTML the decorator must be "foliokit"
 *
 * * Frontend:
 * We look for "component" in query parameters.
 * - If there is a bootstrapped component with the query parameter value we dispatch it
 * - If there is no shortcode/block in the current page and the request format is HTML the decorator must be "foliokit"
 *
 * * Frontend via shortcodes or Gutenberg blocks:
 * - Blocks are registered using the bootstrapper config.
 * - BlockPage class calls render method of this class to echo response in the correct place
 *
 * With the following example component registers a block.
 * Then ComFooBlockBar must be created with the "code" config option set.
 * Now you can put [bar] in a Wordpress page to render contents there.
 *
 * ```php
 * return [
 *     'identifiers' => [
 *         'com:base.dispatcher.page' => [
 *             'blocks' => [
 *                 'com:foo.block.bar'
 *              ]
 *          ]
 *      ]
 * ];
 * ```
 *
 * * Frontend via endpoints:
 * You can register endpoints to completely control a URL. This requires permalinks to be on.
 * With the following example siteurl.com/sample-page/ will be routed to the foo component.
 *
 * ```php
 * return [
 *     'identifiers' => [
 *         'com:base.dispatcher.page' => [
 *             'endpoints' => [
 *                  'sample-page' => [
 *                      'route' => 'component=foo&view=bar',
 *                      'title' => 'Sample page title',
 *                  ]
 *              ]
 *          ]
 *      ]
 * ];
 * ```
 *
 *
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Base
 */
final class DispatcherPage extends Library\DispatcherAbstract implements Library\ObjectSingleton
{
    /**
     * Constructor
     *
     * @param  Library\ObjectConfig $config  An optional ObjectConfig object with configuration options.
     */
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_registerBlocks($config->blocks);

        $this->_registerHooks();

        $this->_registerEndpoints($config->endpoints);
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        parent::_initialize($config);

        $config->append([
            'blocks' => [
                //'com:base.block.include'
            ],
            'endpoints' => []
        ]);
    }

    /**
     * Registers the admin menu pages in Wordpress
     *
     * @param Library\ControllerContext $context
     */
    protected function _actionSet_admin_menu(Library\ControllerContext $context)
    {
        $pages        = new Library\ObjectConfig();
        $bootstrapper = $this->getObject('object.bootstrapper');
        $components   = $bootstrapper->getComponents('admin');

        foreach($components as $component)
        {
            try
            {
                $identifier = $component.'.controller.toolbar.admin';

                if (\Foliokit::getClass($identifier))
                {
                    $commands = $this->getObject($identifier)->getCommands();

                    if ($commands) $pages->append($commands);
                }
            }
            catch (\Exception $e) {}
        }

        $this->_registerAdminpages($pages);
    }

    protected function _actionInit(Library\DispatcherContext $context)
    {
        $query = $this->getRequest()->getQuery();

        // Only run when component parameter exists in the URL.

        if ($query->has('component')) {
            $this->dispatch();
        }
    }

    protected function _actionDispatch(Library\DispatcherContext $context)
    {
        try {
            $request = $this->getObject('request');

            if($request->getQuery()->has('component'))
            {
                $this->_loadFunctionsPhp();

                $component = $request->getQuery()->get('component', 'cmd');
                $domain    = WP::is_admin() ? 'admin' : 'site';

                if ($this->getObject('object.bootstrapper')->isBootstrapped($component, $domain))
                {
                    // Remove wpautop to be able to control the page output
                    // See: https://davidwalsh.name/disable-autop
                    WP::remove_filter( 'the_content', 'wpautop' );

					$dispatcher = $this->getObject(sprintf('com://%s/%s.dispatcher', $domain, $component));

                    if ($domain == 'site')
                    {
                        /*if ($context->param->endpoint) {
                            $blocks = $this->getConfig()->blocks; // Push all blocks on endpoint context
                        } else {
                            $blocks = $context->param->block ? [$context->param->block] : [];
                        }*/

						$dispatcher->addBehavior('optionable', [
							'options' => $context->param->options ?? [],
							'blocks'  => $context->param->block ? [$context->param->block] : $this->getConfig()->blocks
							//'endpoint' => $context->param->endpoint ?? false
						]);
                    }

                    $dispatcher->dispatch();
                }
            }
        }
        catch (\Exception $e)
        {
            if ($e instanceof Library\ControllerExceptionFormatNotSupported) {
                throw $e; // Re-throw 
            } else {
                $this->getObject('exception.handler')->handleException($e);
            }
        }

        return true;
    }

    protected function _actionRender($context)
    {
        return $this->getObject('response')->getContent();
    }

    protected function _actionRender_admin(Library\ControllerContext $context)
    {
        if (!isset($context->page)) {
            throw new \RuntimeException('Page is required in the context');
        }

        $request = $this->getObject('request');

        if ($request->getQuery()->has('component')) {
            // Dispatch action ran already. Return the result
            echo $this->render();
        }
        else
        {
            parse_str((string) $context->page->route, $query);
            $query['page'] = $context->page->page;

            WP::wp_redirect((string)$request->getUrl()->setQuery($query));
        }
    }

    protected function _loadFunctionsPhp()
    {
        $path = \EasyDocLabs\WP\CONTENT_DIR.'/easydoclabs/functions.php';

        if (file_exists($path)) {
            require_once $path;
        }
    }

    protected function _registerEndpoints($endpoints)
    {
        if (count($endpoints) && !WP::is_admin())
        {
            WP::add_filter('do_parse_request', function($bool, $wp) use($endpoints)
            {
                if (WP::get_option('permalink_structure'))
                {
                    $home_path    = parse_url( WP::home_url(), PHP_URL_PATH );
                    $request_path = preg_replace( "#^/?{$home_path}/#", '/', WP::wp_parse_url(WP::add_query_arg([]))['path']);

                    if (strpos($request_path, '/index.php') === 0) {
                        $request_path = substr($request_path, 10);
                    }

                    if (empty($request_path) || $request_path === '/') {
                        return $bool;
                    }
                }
                else
                {
                    $query = $this->getObject('request')->getQuery();

                    if (!$query->has('endpoint')) {
                        return $bool;
                    }

                    $request_path = '/' . $query->get('endpoint', 'string');
                }

                foreach ($endpoints as $path => $options)
                {
                    $path         = rtrim('/' . $path, '/');
                    $request_path = rtrim($request_path, '/');

                    if (strpos($request_path . '/', $path . '/') !== 0) {
                        continue;
                    }

                    $route = $options->route;

                    if (is_string($route)) {
                        parse_str($route, $route);
                    }

                    if (!isset($route['component'])) {
                        return $bool;
                    }

                    $router_path = trim(str_replace($path, '', $request_path), '/');

                    $route['route'] = $router_path;
                    $route['endpoint'] = trim($path, '/');

                    $request = $this->getObject('request');
                    $request->getQuery()->add($route);

                    $this->dispatch();

                    $content = $this->render();

                    $post = new \EasyDocLabs\WP\Post( (object) [
                        'ID'             => 0,
                        'post_title'     => $options->title ?: '',
                        'post_name'      => trim($path, '/'),
                        'post_content'   => $content,
                        'post_excerpt'   => '',
                        'post_parent'    => 0,
                        'menu_order'     => 0,
                        'post_type'      => 'page',
                        'post_status'    => 'publish',
                        'comment_status' => 'closed',
                        'ping_status'    => 'closed',
                        'comment_count'  => 0,
                        'post_password'  => '',
                        'to_ping'        => '',
                        'pinged'         => '',
                        'guid'           => WP::home_url($path),
                        'post_date'      => WP::current_time( 'mysql' ),
                        'post_date_gmt'  => WP::current_time( 'mysql', 1 ),
                        'post_author'    => 0,
                        'is_easydoc_page' => true,
                        'filter'         => 'raw'
                    ] );

                    WP::do_action( 'parse_request', $wp );

                    WP::add_filter('pre_render_block', function($pre_render, $parsed_block) {
                        if (\is_array($parsed_block) && !empty($parsed_block['blockName']) && $parsed_block['blockName'] === 'core/post-comments') {
                            return '';
                        }

                        return $pre_render;
                    }, 10, 2);

                    $wp_query = WP::global('wp_query');

                    $wp_query->init();
                    $wp_query->is_page       = true;
                    $wp_query->is_singular   = true;
                    $wp_query->is_home       = false;
                    $wp_query->found_posts   = 1;
                    $wp_query->post_count    = 1;
                    $wp_query->max_num_pages = 1;

                    $wp_query->posts          = [$post];
                    $wp_query->post           = $post;
                    $wp_query->queried_object = $post;
                    $wp_query->easydoc_page   = $post;

                    $GLOBALS['post']          = $post;

                    WP::do_action('wp', $wp);

                    // Locate the template (or the override coming from the filters)
                    $template = WP::locate_template(['page.php', 'index.php']);

					if (WP::current_theme_supports('block-templates')) {
						$template = WP::locate_block_template($template, 'page', ['page.php', 'index.php']);
					}

                    $filtered = WP::apply_filters('template_include', $template);

                    if (empty($filtered) || file_exists($filtered)) {
                        $template = $filtered;
                    }

                    if (!empty($template) && file_exists($template)) {
                        require_once $template;
                    }

                    exit();
                }

                return $bool;
            }, 1, 2 );
        }
    }

    /**
     * Handles page save and delete actions
     */
    protected function _registerHooks()
    {
        WP::add_action('admin_menu', function() {
            $this->set_admin_menu();
        });
    }

    /**
     * Adds the pages to the Wordpress admin menu
     *
     * @param $pages
     */
    protected function _registerAdminpages($pages)
    {
        $route = function($page) {
            $context    = $this->getContext();
            $context->page = $page;

            $this->execute('render_admin', $context);
        };

        // Add the pages
        foreach ($pages as $page)
        {
            $page = (object) $page;

            WP::add_menu_page(
                $page->title,
                $page->title,
                $page->permission,
                $page->page,
                function () use ($route, $page) { $route($page); },
                $page->icon ?: ''
            );

            if ($page->pages)
            {
                foreach ($page->pages as $subpage)
                {
                    $subpage = (object) $subpage;

                    WP::add_submenu_page(
                        $page->page,
                        $subpage->title,
                        $subpage->title,
                        $subpage->permission,
                        $subpage->page,
                        isset($subpage->route) ? function () use ($route, $subpage) { $route($subpage); } : null
                    );
                }

            }
        }
    }

    protected function _registerBlocks($blocks)
    {
        /** @var $registry BlockRegistry */
        $registry = $this->getObject('com:base.block.registry');
        $blocks   = (array) Library\ObjectConfig::unbox($blocks);

        foreach ($blocks as $key => $value)
        {
            if (is_numeric($key)) {
                $registry->registerBlock($value);
            } else {
                $registry->registerBlock($key, $value);
            }
        }
    }
}
