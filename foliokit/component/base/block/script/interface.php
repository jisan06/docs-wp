<?php
/**
 * Foliokit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

/**
 * Block script interface
 *
 * @author  Ercan Ozkaya <http://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Base
 */
interface BlockScriptInterface
{
    public function getScript();

    public function getDependencies();

    public function addDependencies(array $dependencies);

    public function addDependency($dependency);

    public function getBlockConfiguration();

    public function beforeEnqueue();

    /**
     * @return BlockInterface
     */
    public function getBlock();

    /**
     * @param BlockInterface $block
     */
    public function setBlock(BlockInterface $block);
}