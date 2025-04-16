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
 * Url Template Filter
 *
 * Filter allows to create url schemes that are replaced on compile and render.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class TemplateFilterVersion extends Library\TemplateFilterAbstract
{
    /**
     * A component => version map
     *
     * @var array
     */
    protected static $_versions = [];

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   Library\ObjectConfig $config Configuration options
     * @return  void
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'priority' => Library\TemplateFilterInterface::PRIORITY_HIGH
        ]);

        parent::_initialize($config);
    }


    /**
     * Returns the version information of a component
     *
     * @param $component
     * @return string|null
     */
    protected function _getVersion($component)
    {
        if (!isset(self::$_versions[$component]))
        {
            try
            {
                if (in_array($component, ['base', 'css', 'js'])) {
                    $version = \Foliokit::VERSION;
                } else {
                    $version = $this->getObject('com:'.$component.'.version')->getVersion();
                }
            }
            catch (\Exception $e) {
                $version = null;
            }

            self::$_versions[$component] = $version;
        }

        return self::$_versions[$component];
    }

    /**
     * Adds version suffixes to stylesheets and scripts
     *
     * {@inheritdoc}
     */
    public function filter(&$text, Library\TemplateInterface $template)
    {
        $pattern = '~
            <ktml:(?:script|style) # match ktml:script and ktml:style tags
            [^(?:src=)]+           # anything before src=
            src="                  # match the link
              (
              assets://           # starts with media:// or assets://
              ([^/]+)/            # match the extension (or js|css for foliokit)
              [^"]+               # match the rest of the link
              )"                
             (.*)/>
        ~siUx';

        if(preg_match_all($pattern, $text, $matches, PREG_SET_ORDER))
        {
            foreach ($matches as $match)
            {
                $version = $this->_getVersion($match[2]);

                if ($version)
                {
                    $url     = $match[1];
                    $version = substr(md5($version), 0, 8);

                    // ver=$version is the WP standard for appending version numbers on assets (code depends on it)
                    $suffix  = (strpos($url, '?') === false ? '?' : '&') . 'ver=' . $version;

                    $text    = str_replace($url, $url.$suffix, $text);
                }
            }
        }
    }
}