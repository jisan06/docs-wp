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
 * Component Template Locator
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Template\Locator
 */
class TemplateLocatorComponent extends Library\TemplateLocatorComponent
{
    protected $_override_path;

    protected $_override_template;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_override_path     = $config->override_path;
        $this->_override_template = $config->override_template;
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'override_path'     => sprintf('%s%2$seasydoclabs%2$stemplates%2$s%%s%2$s', WP_CONTENT_DIR, DIRECTORY_SEPARATOR),
            'override_template' => sprintf('<Path>%s<File>.<Format>.<Type>', DIRECTORY_SEPARATOR)
        ]);

        parent::_initialize($config);
    }

    public function getPathTemplates($url)
    {
        $templates = parent::getPathTemplates($url);

        $override_template = $this->_override_template;

        $info = $this->parseUrl($url);
        $path = $this->_getOverridePath($url);

        //Qualify relative path

        if(substr($override_template, 0, 1) !== '/')
        {
            if (!isset($info['domain']))
            {
                // Check for both base folder and root templates override folder

                $override_template = [
                    $path . $override_template,
                    $path . 'base' . DIRECTORY_SEPARATOR . $override_template
                ];
            }
            else $override_template = $path . $info['domain'] . DIRECTORY_SEPARATOR . $override_template;
        }

        $override_template = (array) $override_template;

        foreach ($override_template as $template) {
            array_unshift($templates, $template);
        }

        return $templates;
    }

    protected function _getOverridePath($url)
    {
        $info = $this->parseUrl($url);

        return sprintf($this->_override_path, $info['package']);
    }
}