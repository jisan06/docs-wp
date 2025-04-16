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

/**
 * Abstract block
 *
 * @author  Ercan Ozkaya <http://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Base
 */
abstract class BlockAbstract extends Library\ObjectAbstract implements BlockInterface
{
    protected $_has_shortcode;

    protected $_name;

    protected $_title;

    protected $_description;

    protected $_block_category;

    protected $_icon;

    protected $_supports;

    protected $_post_types = [];

	protected $_attributes;

    /**
     * @var BlockScriptAbstract
     */
    protected $_script;

    protected static $_block_categories = ['common', 'formatting', 'layout', 'widgets', 'embed'];

    public function __construct(Library\ObjectConfig $config)
    {
        $manager = $config->object_manager;

        $translator = $manager->getObject('translator');
        $identifier = $manager->getIdentifier($config->object_identifier);
        
        $translator->load(sprintf('%s:%s', $identifier->getType(), $identifier->getPackage()));

        parent::__construct($config);

        if (!isset($config->name))
        {
            $name = $this->getIdentifier()->getName();

            if ($name !== 'default') {
                $config->name = $this->getIdentifier()->getName();
            }
        }

		$this->setName($config->name);
		$this->setTitle($config->title);
		$this->setDescription($config->description);
		$this->setIcon($config->icon);
		$this->setCategory($config->category);
		$this->setShortcode($config->shortcode);
		$this->setPostTypes(Library\ObjectConfig::unbox($config->post_types));
		$this->setScript($config->script);
		$this->setAttributes($config->attributes);
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
		$config->append([
			'name'        => null,
			'title'       => null,
			'description' => null,
			'attributes'  => [],
			'icon'        => 'admin-plugins',
			'category'    => 'widgets',
			'shortcode'   => true,
			'script'      => 'com:base.block.script.inline',
			'post_types'  => []
		]);

        parent::_initialize($config);
    }

    public function setScript($script)
    {
        if (is_string($script)) {
            $script = $this->getObject($script);
        }

        if (!$script instanceof BlockScriptAbstract) {
            throw new \UnexpectedValueException('Block must be an instance of BlockScriptAbstract');
        }

        $script->setBlock($this);

        $this->_script = $script;
    }

    public function getScript()
    {
        return $this->_script;
    }

    public function isSupported($context)
    {
        return true;
    }

    public function beforeSave($context)
    {
    }

    public function beforeRegister()
    {
    }

    public function setAttributes($attributes)
    {
        $this->_attributes = $attributes;

        foreach ($this->_attributes as $name => $config)
        {
            if ($config->control == 'autocomplete') {
                $this->_attributes->append([sprintf('%s_cache', $name) => ['type' => $config->multiple ? 'array' : 'object']]);
            }
        }

        return $this;
    }

    public function getAttributes()
    {
		return Library\ObjectConfig::unbox($this->_attributes);
    }

    public function getSupports()
    {
        return ['customClassName' => false];
    }

    public function getBlockName()
    {
        return $this->getNamespace().'/'.$this->getName();
    }

    public function getShortcodeName()
    {
        return $this->getNamespace().'-'.$this->getName();
    }

    public function hasShortcode()
    {
        return $this->_has_shortcode;
    }

    public function setShortcode($has_shortcode)
    {
        $this->_has_shortcode = $has_shortcode;
    }

	public function render($context)
	{
		$view = $this->getObject('com:easydoc.view.default.html');

		$template = $view->getTemplate()
            ->addFilter('style')
            ->addFilter('script')
			->registerFunction('translate', [$this->getObject('translator'), 'translate']);

        return $template->render('com:base/block/default.html', ['block' => $this, 'context' => $context]);
	}

    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        if (!preg_match('/^[A-Za-z0-9\-]+$/', $name)) {
            throw new \UnexpectedValueException('Block name can only have A-Z0-9\- characters');
        }

        $this->_name = strtolower($name);
    }

    public function getTitle()
    {
        return $this->_title ?: $this->getName();
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->_description = $description;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->_icon;
    }

    /**
     * @param mixed $icon
     */
    public function setIcon($icon)
    {
        $this->_icon = $icon;
    }

    /**
     * @return string Possible values are
     */
    public function getCategory()
    {
        return $this->_block_category;
    }

    /**
     * @param string $block_category. Possible values: ['common', 'formatting', 'layout', 'widgets', 'embed']
     */
    public function setCategory($block_category)
    {
        if (in_array($block_category, static::$_block_categories)) {
            $this->_block_category = $block_category;
        }
    }

    public function getPostTypes()
    {
        return $this->_post_types;
    }

    /**
     * @param array $post_types
     */
    public function setPostTypes($post_types)
    {
        $this->_post_types = $post_types;
    }

    public function getNamespace()
    {
        return BlockInterface::BLOCK_NAMESPACE;
    }
}
