<?php
/**
 * Foliokit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Library;

/**
 * Abstract block script
 *
 * @author  Ercan Ozkaya <http://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Base
 */
abstract class BlockScriptAbstract extends Library\ObjectAbstract implements BlockScriptInterface
{
    /**
     * @var BlockInterface
     */
    protected $_block;

    /**
     * @var array
     */
    protected $_dependencies = [];

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        if ($config->block) {
            $this->setBlock($config->block);
        }

        if ($config->dependencies) {
            $this->addDependencies(Library\ObjectConfig::unbox($config->dependencies));
        }
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'block' => null,
            'dependencies' => [
                'wp-dom-ready',
                'wp-api-fetch',
                'wp-blocks',
                'wp-i18n',
                'wp-editor',
                'wp-element',
                'wp-components',
            ]
        ]);

        parent::_initialize($config);
    }

    abstract public function getScript();

    public function beforeEnqueue()
    {
    }

    public function getBlockConfiguration()
    {
        $t     = $this->getObject('translator');
        $block = $this->getBlock();

        return [
            'title'       => $t($block->getTitle()),
            'description' => $t($block->getDescription()),
            'category'    => $block->getCategory(),
            'icon'        => $block->getIcon(),
            'attributes'  => $block->getAttributes(),
            'supports'    => $block->getSupports()
        ];
    }

    public function getDependencies()
    {
        return $this->_dependencies;
    }

    public function setDependencies(array $dependencies)
    {
        $this->_dependencies = $dependencies;
    }

    public function addDependencies(array $dependencies)
    {
        $this->_dependencies = array_merge($dependencies, $this->_dependencies);
        $this->_dependencies = array_unique($this->_dependencies);
    }

    public function addDependency($dependency)
    {
        $this->addDependencies([$dependency]);
    }

    /**
     * @return BlockInterface
     */
    public function getBlock()
    {
        return $this->_block;
    }

    /**
     * @param BlockInterface $block
     */
    public function setBlock(BlockInterface $block)
    {
        $this->_block = $block;
    }
}