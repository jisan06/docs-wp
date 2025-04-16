<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

/**
 * Default Controller
 *
 * @author  Ercan Ozkaya <http://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Base
 */
interface BlockInterface
{
    const BLOCK_NAMESPACE = 'foliokit';

    public function isSupported($context);

    public function render($context);

    public function beforeSave($context);

    public function beforeRegister();

    public function getBlockName();

    public function getShortcodeName();

    public function getNamespace();

    public function getName();

    /**
     * @param mixed|string $name
     */
    public function setName($name);

    public function getTitle();

    /**
     * @param string $title
     */
    public function setTitle($title);

    /**
     * @return string Possible values are
     */
    public function getCategory();

    /**
     * @param string $category. Possible values: ['common', 'formatting', 'layout', 'widgets', 'embed']
     */
    public function setCategory($category);

    public function setShortcode($has_shortcode);

    public function hasShortcode();

    /**
     * @return mixed
     */
    public function getIcon();

    /**
     * @param mixed $icon
     */
    public function setIcon($icon);

    /**
     * @return mixed
     */
    public function getDescription();

    /**
     * @param mixed $description
     */
    public function setDescription($description);

    public function setAttributes($attributes);

    public function getAttributes();

    public function getSupports();

    /**
     * @return BlockScriptAbstract
     */
    public function getScript();

    public function setScript($script);

    public function getPostTypes();

    /**
     * @param array $post_types
     */
    public function setPostTypes($post_types);
}
