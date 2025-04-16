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
 * Inline block script
 *
 * Returns the block script directly to be added inline
 *
 * @author  Ercan Ozkaya <http://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Base
 */
class BlockScriptInline extends BlockScriptAbstract
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
        ]);

        parent::_initialize($config);
    }

    public function getScript()
    {
        $block = $this->getBlock();

        $blockname = $block->getBlockName();
        $config    = json_encode($this->getBlockConfiguration());

        return "(function() {
            var config = {$config};
            
            config.edit = {$this->getEditScript()};
            config.save = {$this->getSaveScript()};
            
            wp.blocks.registerBlockType('{$blockname}', config);
            
        })();";
    }

    public function getEditScript()
    {
        $block = $this->getBlock();

        $placeholder = json_encode([
            'label' => $block->getTitle(),
            'icon'  => $block->getIcon(),
            'instructions' => $block->getDescription(),
        ]);

        return "function(props) {
            return wp.element.createElement(wp.components.Placeholder, {$placeholder});
        }";
    }

    public function getSaveScript()
    {
        return "function()  {
            return null; //save has to exist. This all we need
        };";
    }

}