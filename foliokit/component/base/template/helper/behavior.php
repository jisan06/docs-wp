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
 * Behavior Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class TemplateHelperBehavior extends Library\TemplateHelperBehavior implements Library\TemplateHelperParameterizable
{
    /**
     * Loads jQuery under a global variable called kQuery.
     *
     * If debug config property is set, an uncompressed version will be included.
     *
     * You can do window.jQuery = window.$ = window.kQuery; to use the default names
     *
     * @param array|Library\ObjectConfig $config
     * @return string
     */
    public function jquery($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'debug' => \Foliokit::getInstance()->isDebug(),
        ]);

        $html = '';

        $script = $this->getObject('com:base.template.helper.script');

        if (!static::isLoaded('jquery'))
        {
            if ($config->decorator === 'foliokit')
            {
                $html .= $script->load(['name' => 'jquery', 'enqueue' => false]);
                $html .= $this->buildElement('ktml:script', ['src' => 'assets://js/foliokit.kquery.js']);
            }
            else 
            {
                $src = EASY_DOCS_URL.'foliokit/library/resources/assets/js/foliokit.kquery.js';
                
                WP::wp_enqueue_script('foliokit.kquery', $src, [$script->map('jquery')], \Foliokit::VERSION);
                
                $script->load(['name' => 'jquery']);
            }

            static::setLoaded('jquery');
        }

        return $html;
    }

    /**
     * Loads the calendar behavior and attaches it to a specified element
     *
     * @param array|Library\ObjectConfig $config
     * @return string   The html output
     */
    public function calendar($config = [])
    {
        $config = new Library\ObjectConfigJson($config);
        $config->append([
            'server_timezone' => WP::wp_timezone(),
        ]);

        return parent::calendar($config);
    }

}
