<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;
use EasyDocLabs\WP;

class TemplateHelperBehavior extends Base\TemplateHelperBehavior
{
    public function multidownload($config = []) {
        $config = new Library\ObjectConfigJson($config);
        $config->append([]);

        $html = '';

        if (!static::isLoaded('easydoc-multidownload'))
        {
            $html = $this->jquery($config);
            $html .= $this->tooltip($config);
            $html .= $this->getTemplate()->helper('translator.script', ['strings' => [
                'Preparing download',
                'You are not authorized to download the selected file',
                'Remote files cannot be downloaded in batch'
            ]]);
            $html .= '<ktml:script src="assets://easydoc/js/multidownload.js" />';
            $html .= '
            <style>
                /* get rid of the red-on-white button on hover */
                .k-js-multi-download.disabled:hover {
                    background-color: inherit !important;
                    color: inherit !important;
                }
            </style>
            ';

            static::setLoaded('easydoc-multidownload');
        }

        return $html;
    }

    /**
     * A feature to tick multiple checkboxes at the same time
     * Tick a range of checkboxes by holding the 'shift' key
     * 
     * @param mixed $config
     * @return string
     */
    public function multiselect($config = [])
    {
        $config = new Library\ObjectConfigJson($config);

        $config->append([
            'selector' => '.k-js-item-select'
        ]);

        $html = '';

        if (!static::isLoaded('easydoc-multiselect'))
        {
            $html .= '<ktml:script src="assets://easydoc/js/multiselect.js" />';
            $html .= "<script>
                kQuery(function($){
                    $('{$config->selector}').multiselect();
                });
                </script>";
            
            static::setLoaded('easydoc-multiselect');
        }

        return $html;
    }

    public function downloadlabel($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'params' => []
        ])->append([
            'force_download' => $config->params->force_download,
            'gdocs_supported_extensions' => $this->getObject('com://site/easydoc.controller.behavior.previewable')->getGooglePreviewExtensions(),
            'gdocs_preview' => (int) $config->params->preview_with_gdocs
        ]);

        $html = '';

        unset($config->params);

        $signature = md5(serialize([$config->gdocs_preview, $config->gdocs_supported_extensions]));
        if (empty($config->force_download) && !static::isLoaded($signature)) {

            unset($config->params);

            $html .= $this->getTemplate()->helper('translator.script', ['strings' => ['Play', 'View', 'Open']]);

            $html .= "
            <ktml:script src=\"assets://easydoc/site/js/downloadlabel.js\" />
            <script>
                kQuery(function($) {
                    $('a.easydoc_download__button').downloadLabel($config);
                });
            </script>
            ";

            static::setLoaded($signature);
        }

        return $html;
    }

    public function photoswipe($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'template_url' => 'com://site/easydoc/document/photoswipe.html'
        ]);

        $html = $this->getTemplate()->render($config->template_url);

        return $html;
    }

    /**
     * Shorthand to use in template files in frontend
     *
     * @param array $config
     * @return string
     */
    public function thumbnail_modal($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'selector' => '.k-ui-namespace .thumbnail',
            'options'  => [
                'type' => 'image'
            ]
        ]);

        return $this->modal($config);
    }

    /**
     * Uses Google Analytics to track download events in frontend
     * @param array $config
     * @return string
     */
    public function download_tracker($config = [])
    {
        $config = new Library\ObjectConfigJson($config);

		$config->append([
			'selector' => 'easydoc_track_download',
			'context'  =>  'EasyDocs',
			'action'   => 'Download'
		]);

        $html = $this->jquery($config);

        $signature = md5(serialize([$config->selector, $config->context, $config->action]));
        if (!static::isLoaded($signature)) {
            $html .= "
            <script>
            kQuery(function($) {
                $('.{$config->selector}').on('click', function() {
                    var el = $(this);

                    if (typeof gtag !== 'undefined') {
                        gtag('event', '{$config->action}', {
                            'event_category': '{$config->context}',
                            'event_label': el.data('title'),
                            'name': el.data('title'),
                            'value': parseInt(el.data('id'), 10)
                        });
                    }
                    else if (typeof window.GoogleAnalyticsObject !== 'undefined' && typeof window[window.GoogleAnalyticsObject] !== 'undefined') {
                        window[window.GoogleAnalyticsObject]('send', 'event', '{$config->context}', '{$config->action}', el.data('title'), parseInt(el.data('id'), 10));
                    }
                    else if (typeof _gaq !== 'undefined' && typeof _gat !== 'undefined') {
                        if (_gat._getTrackers().length) {
                            _gaq.push(function() {
                                var tracker = _gat._getTrackers()[0];
                                tracker._trackEvent('{$config->context}', '{$config->action}', el.data('title'), parseInt(el.data('id'), 10));
                            });
                        }
                    }
                });

                if (typeof _paq !== 'undefined') {
                    _paq.push(['setDownloadClasses', '{$config->selector}']);
                    _paq.push(['trackPageView']);
                }
            });
            </script>
            ";
            static::setLoaded($signature);
        }

        return $html;
    }

    /**
     * Makes links delete actions
     *
     * Used in frontend delete buttons
     *
     * @param array $config
     * @return string
     */
    public function deletable($config = [])
    {
        $config = new Library\ObjectConfigJson($config);

		$config->append([
			'selector' => sprintf('.easydoc-deletable-%s', $config->item ?? 'item'),
			'confirm_message' => $this->getObject('translator')->translate('You will not be able to bring this item back if you delete it. Would you like to continue?', ['item' => $config->item ?? 'item']),
		]);

        $html = $this->foliokit();

        $signature = md5(serialize([$config->selector,$config->confirm_message]));
        if (!static::isLoaded($signature))
        {
            $html .= "

            <script>

                kQuery(function($)
                {
                    $('{$config->selector}').on('click', function(event)
                    {
                        event.preventDefault();

                        var target = $(event.target);

                        if (!target.hasClass('k-is-disabled') && confirm('{$config->confirm_message}')) {
                            new Koowa.Form($.parseJSON(target.prop('rel'))).submit();
                        }
                    });

                    let buttons = document.querySelectorAll('{$config->selector}');

                    for (const button of buttons)
                    {
                        if (typeof button.dataset.documentsCount !== 'undefined')
                        {
                            document.body.addEventListener('easydoc_item_deleted', (event) =>
                            {
                                button.dataset.documentsCount--;

                                if (button.dataset.documentsCount == 0)
                                {
                                    button.classList.remove('k-is-disabled');
                                    button.classList.remove('disabled');
                                }
                            });
                        }
                    }
                });

            </script>
            ";

            static::setLoaded($signature);
        }

        return $html;
    }

    public function scanner($config = [])
    {
        $connect    = $this->getObject('com://admin/easydoc.model.entity.config')->connectAvailable();
        $extensions = \ComEasyDocControllerBehaviorScannable::$ocr_extensions;

        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'options'  => [
                'scannableExtensions' => $extensions,
                'isAdmin'             => $this->getObject('user')->authorise('core.manage', 'com_easydoc') === true,
                'isConnectEnabled'    => $connect,
            ]
        ]);

        return $this->getTemplate()->render('com://admin/easydoc/document/scanner.html', ['config' => $config]);
    }

    /**
     * Widget for selecting an thumbnail image
     *
     * @param array $config
     * @return string
     */
    public function thumbnail($config = [])
    {
        $thumbnail_controller = $this->getObject('com:easydoc.controller.thumbnail');

        $connect = $this->getObject('com:easydoc.model.entity.config')->connectAvailable();

        $config = new Library\ObjectConfigJson($config);

        $config->append([
            'entity' => null
        ])->append([
            'entity_type' => Library\StringInflector::singularize($config->entity->getIdentifier()->name),
        ])->append([
            'options'  => [
                'isAdmin'           => $this->getObject('user')->authorise('core.manage', 'com_easydoc') === true,
                'hasConnectSupport' => $connect,
                'hasWebSupport'     => $connect,
                'connect_token'     => $connect ? $this->getObject('connect')->generateToken() : false,
                'automatic'  => [
                    'exists'     => is_file(sprintf('%s/easydoclabs/easydoc-images/%s', \EasyDocLabs\WP\CONTENT_DIR, $thumbnail_controller->getDefaultFilename($config->entity))),
                    'path'       => $thumbnail_controller->getDefaultFilename($config->entity),
                    'enabled'    => true, //($config->entity_type === 'document' && $this->getObject('com:easydoc.model.configs')->fetch()->thumbnails),
                    'extensions' => $thumbnail_controller->getSupportedExtensions(),
                ],
                'image_container'      => 'easydoc-images',
                'image_folder'         => 'root://wp-content/easydoclabs/easydoc-images/',
                'links' => [
                    'web'    => 'https://connect.system.ait-themes.club/image-picker/',
                    'custom' => (string) $this->getTemplate()->route('component=easydoc&page=easydoc-documents&view=files&layout=select&types[]=image', false, false),
                    'save_web_image' => (string) $this->getTemplate()->route('component=easydoc&page=easydoc-documents&view=file&format=json&routed=1', false, false),
                    'preview_automatic_image' => (string) $this->getTemplate()->route('component=easydoc&page=easydoc-documents&view=file&container=easydoc-files&routed=1', false, false)
                ]
            ]
        ])->append([
            'options'  => [
                'editor' => [
                    'site'    => $this->getObject('connect')->getSite(),
                    'baseUrl' => (string) $this->getObject('connect')->getRoute('view=connect&task=image-editor')
                ]
            ]
        ]);

        return $this->getTemplate()->render('com://admin/easydoc/document/thumbnail.html', ['config' => $config]);
    }


    /**
     * Loading js necessary to render a jqTree sidebar navigation of document categories
     *
     * @param array|Library\ObjectConfig $config
     * @return string	The html output
     */
    public function category_tree($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'debug'          => \Foliokit::isDebug(),
            'element'        => '.k-js-category-tree',
            'selected'       => '',
            'document_count' => false,
            'route' => ['view' => 'categories']
        ])->append([
            'options' => [
                'selected' => $config->selected
            ]
        ]);

        $router = $this->getObject('router');
        $route  = $config->route->toArray();
        $key    = $config->options->state ?: 'category';

        $map = function(&$data, $category, $config, $categories) use($router, $key)
        {
            $parts = explode('/', $category->path);
            array_pop($parts); // remove current id
            $data[] = [
                'label'  => $category->title.(isset($category->_documents_count) ? ' ('.$category->_documents_count.')' : ''),
                'id'     => (int)$category->id,
                'slug'   => $category->slug,
                'path'   => $category->path,
                'level'  => count($parts)+1,
                'parent' => (int)array_pop($parts),
            ];
        };

        $data = $this->getObject('com:easydoc.template.helper.listbox')->fetchCategories($config, $map);

        $tree = [];
        $tree[0] = (object)[
            'label' => $this->getObject('translator')->translate('All Categories'),
            'id' => 0,
            'open' => true,
            'selected' => !$config->selected,
            'children' => [],
            'level' => 0,
            'route'  => (string) $router->generate('easydoc:', array_merge($route, [$key => ''])),
        ];

        $latest = null;

        foreach ($data as $c) {
            $c = (object) $c;
            $c->route = (string) $router->generate('easydoc:', array_merge($route, [$key => $c->id]));
            $parts = explode('/', $c->path);
            array_pop($parts); // remove current id

            $c->selected = $c->id == $config->selected;
            $c->open     = $c->selected;
            $c->children = [];

            $node =& $tree[0];

            foreach ($parts as $part)
            {
                $part = (int) $part;

                if (isset($node->children[$part]))
                {
                    $node =& $node->children[$part];

                    if ($c->selected) {
                        $node->open = true;
                    }
                }
            }

            $c->level = $node->level + 1; // Normalize levels (access permissions constraints can introduce gaps inside the tree)

            $node->children[$c->id] = $c;
        }

        $script = $this->buildElement('script', [], "
                    document.addEventListener('click', function(event) {
                        const closedClass = 'jqtree-closed';

                        if (event.target.matches('.k-js-tree-toggler')) {
                            const toggler = event.target;

                            if (toggler.classList.contains(closedClass)) {
                                toggler.closest('li').classList.remove(closedClass);
                                toggler.classList.remove(closedClass);
                            } else {
                                toggler.closest('li').classList.add(closedClass);
                                toggler.classList.add(closedClass);
                            }
                        }

                        if (event.target.matches('.k-js-tree-clickable')) {
                            const a = event.target.querySelector('a.k-js-tree-link');

                            if (a) { a.click(); }
                        }
                    });");

        $whitespace = $this->buildElement('i', ['class' => 'jqtree-whitespace']);
        $openToggle = $this->buildElement('a',[
            'class' => 'k-js-tree-toggler jqtree-toggler jqtree_common jqtree-toggler-left',
            'role' => 'presentation', 'aria-hidden' => 'true'
        ],'▼');
        $closeToggle = $this->buildElement('a',[
            'class' => 'k-js-tree-toggler jqtree-toggler jqtree_common jqtree-toggler-left jqtree-closed',
            'role' => 'presentation', 'aria-hidden' => 'true'
        ],'▼');

        $outputNode = function ($category) use (&$outputNode, $whitespace, $openToggle, $closeToggle) {
            if ($category->children) {
                $childrenOutput = array_map($outputNode, $category->children);
                $children = $this->buildElement('ul', ['class'=>'jqtree_common', 'role' => 'group'], $childrenOutput);
            } else {
                $children = '';
            }

            $link = $this->buildElement('a', [
                'class' => 'k-js-tree-link jqtree-title jqtree_common jqtree-title-folder', 'role' => 'treeitem',
                'aria-level' => $category->level, 'aria-selected' => $category->selected ? 'true' : 'false', 'aria-expanded' => $category->open ? 'true' : 'false',
                'style' => 'text-decoration:none; color:inherit;', 'href' => $category->route
            ], $category->label);

            $classes = ['jqtree_common', 'jqtree-folder'];

            if (!$category->open) { $classes[] = 'jqtree-closed'; }
            if ($category->selected) { $classes[] = 'jqtree-selected k-is-active'; }

            return $this->buildElement('li', ['class' => $classes, 'role' => 'presentation'],
                [$this->buildElement('div', ['class' => 'k-js-tree-clickable jqtree-element jqtree_common', 'role' => 'presentation', 'title' => $category->label], [
                    str_repeat($whitespace, $category->level),
                    ($category->children ? ($category->open ? $openToggle : $closeToggle) : $whitespace),
                    $link,
                ]),
                $children]
            );
        };

        return $script .
            $this->buildElement('ul', ['class'=>'jqtree_common jqtree-tree', 'role' => 'tree'], $outputNode($tree[0]));

    }

    public function category_tree_site($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'debug'    => \Foliokit::isDebug(),
            'element'  => '.k-js-category-tree',
            'selected' => '',
            'state'    => [],
            'model'    => 'com://site/easydoc.model.categories'
        ])->append([
            'options' => [
                'selected' => $config->selected
            ]
        ]);

        $page    = $config->state->page;

        $router = $this->getObject('router.site');

        $map = function(&$data, $category, $config) use ($page, $router) {
            $parts = explode('/', $category->path);
            array_pop($parts); // remove current id
            $data[] = [
                'label'      => $category->title,
                'id'         => (int) $category->id,
                'route'      => (string) $router->generate($category),
                'slug'       => $category->slug,
                'path'       => $category->path,
                'parent'     => (int) array_pop($parts),
                'created_on' => $category->created_on
            ];
        };

        $sort = function(&$categories) use ($config)
        {
            if ($config->state->direction == 'desc')
            {
                // We need to sort the resulting categories (orderable does not support reverse ordering for trees)
    
                $tree = [(object) ['id' => 0, 'children' => []]];
    
                foreach ($categories as $category)
                {
                    $category = (object) $category;
        
                    $category->children = [];
        
                    $parts = explode('/', $category->path);
                    array_pop($parts); // remove current id
        
                    $node =& $tree[0];
        
                    foreach ($parts as $part)
                    {
                        $part = (int) $part;
        
                        if (isset($node->children[$part])) {
                            $node =& $node->children[$part];
                        }
                    }
        
                    $node->children[$category->id] = $category;
                }
        
                $sort_categories = function(&$categories) use (&$sort_categories, $config)
                {           
                    $compare_categories = function($a, $b) use ($config)
                    {
                        if ($a->id == $b->id) return 0;
            
                        switch ($config->state->sort)
                        {
                            case 'title':
                                $result = (-1) * strcasecmp($a->label, $b->label);
                                break;
                            case 'created_on':
                                $date_a =  new \DateTime($a->created_on);
                                $date_b =  new \DateTime($b->created_on);
                                $result = $date_a < $date_b;
                                break;
                            default:
                                $result = 0;
                                break;
                        }
    
                        return $result;
                    };
        
                    usort($categories, $compare_categories);
        
                    foreach ($categories as $category) {
                        if ($children =& $category->children) $sort_categories($children);
                    }
        
                    return $categories;
                };
        
                $categories = $sort_categories($tree[0]->children);
            }
        };

        $data = $this->getObject('com:easydoc.template.helper.listbox')->fetchCategories($config, $map, null, $sort);

        $config->options->append(['data' => $data]);

        // Load assets by calling parent tree behavior
        $html = parent::tree(['debug' => $config->debug]);

        if (!static::isLoaded('category_tree_site'))
        {
            $html .= '<ktml:script src="assets://easydoc/site/js/category.tree.js" />';
            $html .= '<script>
                        kQuery(function($){
                            new EasyDoc.Tree.CategoriesSite('.\EasyDocLabs\WP::wp_json_encode($config->element).', '.$config->options.');
                        });</script>';

            static::setLoaded('category_tree_site');
        }

        return $html;
    }

    /**
     * Attaches Bootstrap Affix to the sidebar along with custom code making it responsive
     *
     * @NOTE Also contains j!3.0 specific fixes
     *
     * @TODO requires bootstrap-affix!
     *
     * @param array|KObjectConfig $config
     * @return string	The html output
     */
    public function sidebar($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'sidebar'   => '',
            'target'    => ''
        ]);

        $html = '';
        // Load the necessary files if they haven't yet been loaded
        if (!static::isLoaded('sidebar'))
        {
            $html .= $this->jquery($config);
            //@TODO requires bootstrap-affix!
            //helper('bootstrap.load', array('javascript' => true))
            $html .= '<ktml:script src="assets://easydoc/js/sidebar.js" />';

            static::setLoaded('sidebar');
        }

        $html .= '<script>kQuery(function($){new EasyDoc.Sidebar('.$config.');});</script>';

        return $html;
    }

    public function icon_map($config = [])
    {
        $icon_map = \EasyDocLabs\WP::wp_json_encode(TemplateHelperIcon::getIconExtensionMap());

        $html = "
            <script>
            if (typeof EasyDoc=== 'undefined') EasyDoc= {};

            EasyDoc.icon_map = $icon_map;
            </script>";

        return $html;
    }

    /**
     * Widget for picking an icon
     *
     * Renders as a button that toggles a dropdown menu, with a list over selectable icon thumbs at the top
     * and a Choose Custom button that triggers a modal popup with a file browser for choosing a custom image.
     *
     * Used in document and category forms next to the title input element
     *
     * @param array $config
     * @return string
     */
    public function icon($config = array())
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append(array(
            'name' => '',
            'attribs' => array(),
            'visible' => true
        ))->append(array(
            'options' => array(
                'custom_icon_path'  => 'icon://',
                'blank_icon_path'   => 'media://system/images/blank.png'
            ),
            'icons' => TemplateHelperIcon::getIcons(),
            'id' => $config->name,
            'value' => $config->name
        ))->append(array(
            'options' => array(
                'id' => $config->id
            )
        ));

        $image = $config->value;
        $font_icon = true;

        if (!$image) {
            $image = 'default';
        }

        if (substr($image, 0, 5) === 'icon:') {
            $image = 'icon://'.substr($image, 5);
            $font_icon = false;
        }

        $html = '<ktml:script src="assets://easydoc/js/modal.js" />';

        $html .= $this->modal();

        $html .= '<div class="k-dropdown k-input-group__button">
                        <a class="k-button k-button--default k-dropdown__toggle" data-k-toggle="dropdown" href="javascript:void(0)">
                            <span id="'.$config->id.'-font-preview"
                                  class="k-icon-document-'.($font_icon ? $image : '').'"
                                  style="display:'.($font_icon ? 'inline-block' : 'none').'"
                            ></span>
                            <img
                                id="'.$config->id.'-preview"
                                data-src="'.$image.'"
                                '.($font_icon ? '' : 'src="'.$image.'"').'
                                onerror="this.src=\''.$config->options->blank_image_path.'\'"
                                style="display:'.($font_icon ? 'none' : 'inline-block').'"
                            />
                            <span class="k-caret"></span>
                        </a>
                        <ul class="k-dropdown__menu k-dropdown__menu--grid">';

        foreach($config->icons as $icon)
        {
            $html .= '<li><a class="k-js-document-icon-selector" href="#" title="'.$this->getObject('translator')->translate($icon).'" data-value="'.$icon.'">';
            $html .= '<span class="k-icon-document-'.$icon.' k-icon--size-default"></span>';
            $html .= '<span class="k-visually-hidden">'.$this->getObject('translator')->translate($icon).'</span>';
            $html .= '</a></li>';
        }

        $attribs = $this->buildAttributes($config->attribs);

        $input = '<input name="%1$s" id="%2$s" value="%3$s" %4$s size="40" %5$s style="display:none" />';

        $html .= '<li class="k-dropdown__block-item">';
        $html .= sprintf($input, $config->name, $config->id, $config->value, $config->visible ? 'type="text" readonly' : 'type="hidden"', $attribs);
        $html .= '</li></ul></div>';

        /**
         * str_replace helps convert the paths before the template filter transform media:// to full path
         */
        $options = str_replace('\/', '/', $config->options->toString());

        $html .= $this->icon_map();

        /**
         * str_replace helps convert the paths before the template filter transform media:// to full path
         */
        $html .= "<script>kQuery(function($){new EasyDoc.Modal.Icon(".$options.");});</script>";


        return $html;
    }

    /**
     * Generate opengraph meta tags
     *
     * @param mixed $config
     * @return string
     */
    public function opengraph($config = array())
    {
        $config = new Library\ObjectConfig($config);
        $config
        ->append(array(
            'entity'    => null,
            'site_name' => WP::get_bloginfo('name'),
            'locale'    => WP::get_locale()
        ))
        ->append(array(
            'route'       => $config->url,
            'description' => WP::wp_trim_words( $config->entity->description, 25 ),
            'image'       => $config->entity->image_path,
        ));

        $html = '';

        if (!static::isLoaded('easydoc-opengraph') && !$config->entity->isNew())
        {
            $html .= '<meta content="' . WP::esc_attr($config->locale) . '" property="og:locale" />';
            $html .= '<meta content="article" property="og:type" />';
            $html .= '<meta content="' . WP::esc_attr($config->title) . '" property="og:title" />';
            $html .= '<meta content="' . WP::esc_attr($config->description) . '" property="og:description" />';
            $html .= '<meta content="' . WP::esc_url($config->route) . '" property="og:url" />';
            $html .= '<meta content="' . WP::esc_attr($config->site_name) . '" property="og:site_name" />';

            if ($config->image) {
                $html .= '<meta content="' . WP::esc_url($config->image) . '" property="og:image" />';
            }

            // Set opengraph namespace if not already set
            if (!preg_match("/xmlns:og/", WP::get_language_attributes(), $matches))
            {
                WP::add_filter('language_attributes', function($output) {
                    return $output . ' xmlns:og="http://opengraphprotocol.org/schema/"';
                });
            }

            if (!preg_match("/xmlns:fb/", WP::get_language_attributes(), $matches))
            {
                WP::add_filter('language_attributes', function($output) {
                    return $output . ' xmlns:fb="http://www.facebook.com/2008/fbml"';
                });
            }

            static::setLoaded('easydoc-opengraph');
        }

        return $html;
    }
}
