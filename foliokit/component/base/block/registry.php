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
 * Include block
 *
 * @author  Ercan Ozkaya <http://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Base
 */
class BlockRegistry extends Library\ObjectAbstract implements Library\ObjectSingleton
{
    private $__blocks = [];

    private $__shortcodes = [];

	protected $_theme_edit = null;

    protected function _render(BlockInterface $block, $attributes)
    {
		$context = new Library\ObjectConfig([
			'attributes' => $attributes,
			'generic'    => $this->isThemeEdit()
		]);

        return $block->render($context);
    }

    public function isThemeEdit()
    {
        if (!isset($this->_theme_edit))
        {
            if ($id = $this->_getScreenId())
            {
                // Screen ID checks

                $result = in_array($id, ['customize', 'site-editor']);
            }
            else
            {
                // Plugin dependent checks

                $editors = ['divi_builder', 'customizer'];
                //$editors = ['divi_builder', 'customizer', 'site_editor']; // Site editor check through screen ID seems to work at all times, no need for this extra check

                $result = false;
        
                foreach ($editors as $editor)
                {
                    $method = sprintf('_is%sLoaded', Library\StringInflector::camelize($editor));
        
                    if (method_exists($this, $method)) {
                        $result = $this->{$method}();
                    }

                    if ($result) break;
                }
            }

            $this->_theme_edit = $result;
        }

        return $this->_theme_edit;
    }

    protected function _isDiviBuilderLoaded()
    {
        $query = $this->getObject('request')->getQuery();

        return $query->et_pb_preview == true;
    }

    protected function _isCustomizerLoaded()
    {
        $query = $this->getObject('request')->getQuery();

        return isset($query->customize_changeset_uuid);
    }

    protected function _isSiteEditorLoaded()
    {
        $path = $this->getObject('request')->getUrl()->getPath();

        return basename($path, '.php') === 'site-editor'; 
    }

    protected function _getScreenId()
    {
        global $current_screen;

        $id = false;

        if (isset($current_screen)) {
            $id = $current_screen->id;
        }
        
        return $id;
    }

    public function registerBlock($block, $config = [])
    {
        if($block instanceof BlockInterface) {
            $identifier = $block->getIdentifier();
        } else {
            $identifier = $this->getIdentifier($block);
        }

        //Create the behavior object
        if (!($block instanceof BlockInterface)) {
            $block = $this->getObject($identifier, $config);
        }

        if (!($block instanceof BlockInterface)) {
            throw new \UnexpectedValueException("Block $identifier does not implement BlockInterface");
        }

        $this->__blocks[$block->getBlockName()] = $block;

        $renderer = function($attributes) use($block)
        {
            if (empty($attributes)) { // Shortcodes might pass empty string as $attributes
                $attributes = [];
            }

            $block_attributes = $block->getAttributes();

            foreach ($attributes as $key => $value)
            {
                $is_type_array = isset($block_attributes[$key]) && isset($block_attributes[$key]['type']) && ($block_attributes[$key]['type'] == 'array');

                if ($value === '0')
                {
                    $attributes[$key] = false;
                }
                else if (is_string($value) && $is_type_array)
                {
                    // Convert value into array 

                    if (strpos($value, ',') !== false) {
                        $value = explode(',', $value);
                    } else {
                        $value = (array) $value;
                    }

                    $attributes[$key] = $value;
                }
            }

            try {
                return $this->_render($block, $attributes);
            }
            catch (\Exception $e) {
                if (\Foliokit::isDebug()) {
                    throw $e;
                }

                return '';
            }

        };

        if ($block->beforeRegister() === false) {
            return;
        }

        if ($block->hasShortcode()) {
            $this->__shortcodes[$block->getShortcodeName()] = $block;

            WP::add_shortcode($block->getShortcodeName(), $renderer);
        }

        if (function_exists('register_block_type'))
		{
            $blockname = $block->getBlockName();

			// Only load scripts on Gutenberg editor context

			WP::add_action('enqueue_block_editor_assets', function() use ($block, $blockname)
			{
				if (!$this->isThemeEdit())
				{
					$script = $block->getScript();

					if ($script->beforeEnqueue() === false) {
						return;
					}

					$hide_by_post_type = '';

					if ($post_types = $block->getPostTypes()) {
						$types = json_encode($post_types);
						$hide_by_post_type = "

						(function(wp) {
							wp.domReady(function() {
								var postTypes = $types;
								var hasRun = false;
								wp.data.subscribe(() => {
									if (hasRun === false) {
										var postType = wp.data.select('core/editor').getCurrentPostType();

										if (postType) {
											hasRun = true;
											if (postTypes.indexOf(postType) === -1) {
												wp.data.dispatch( 'core/edit-post' ).hideBlockTypes(['$blockname']);
											} else {
												wp.data.dispatch( 'core/edit-post' ).showBlockTypes(['$blockname']);
											}
										}
									}
								});
							});
						})(wp);

						";
					}

					if ($script instanceof BlockScriptExternal) {
						$block_config = json_encode($script->getBlockConfiguration());
						$block_config_name = str_replace('/', '-', $blockname).'-config';

						WP::wp_register_script($block_config_name, '', []);
						WP::wp_enqueue_script($block_config_name);
						WP::wp_add_inline_script($block_config_name, $hide_by_post_type."(function() {
						if (typeof FoliokitBlockConfigurations === 'undefined') {
							FoliokitBlockConfigurations = {};
						}

						FoliokitBlockConfigurations['$blockname'] = {$block_config};
						})()");

						$dependencies = array_merge([$block_config_name], $script->getDependencies());

						WP::wp_enqueue_script($blockname, EASY_DOCS_URL.''.$script->getScript(), $dependencies);

					}
					elseif ($script instanceof BlockScriptInline)
					{
						WP::wp_register_script($blockname, '', $script->getDependencies());
						WP::wp_enqueue_script($blockname);
						WP::wp_add_inline_script($blockname, $hide_by_post_type.$script->getScript());
					}
				}
			});

            WP::register_block_type($blockname, [
                'editor_script'   => $blockname,
                'render_callback' => $renderer
            ]);
        }
    }

    public function hasShortcode($code)
    {
        return isset($this->__shortcodes[$code]);
    }

    public function hasBlock($code)
    {
        return isset($this->__blocks[$code]);
    }

    public function getBlock($code)
    {
        $result = null;

        if(isset($this->__blocks[$code])) {
            $result = $this->__blocks[$code];
        }

        return $result;
    }

	public function getPageBlocks()
	{
		$blocks = [];

		foreach ($this->__blocks as $block) {
			if ($block instanceof BlockPage) $blocks[] = $block;
		}

		return $blocks;
	}

    public function getBlockByShortcode($code)
    {
        $result = null;

        if(isset($this->__shortcodes[$code])) {
            $result = $this->__shortcodes[$code];
        }

        return $result;
    }

    public function getShortcodes()
    {
        $shortcodes = $this->__shortcodes ?? [];

        return array_keys($shortcodes);
    }
}
