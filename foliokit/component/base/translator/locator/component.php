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
 * Component Translator Locator
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Translator\Locator
 */
class TranslatorLocatorComponent extends Library\TranslatorLocatorComponent implements TranslatorLocatorOverrides
{
    /**
     * The overrides path
     *
     * @var string
     */
    protected $_overrides_path;

    /**
     * The overrides base template
     *
     * @var string
     */
    protected $_overrides_template;

    /**
     * Found overrides locations map
     *
     * @var array
     */
    protected $__override_location_cache = [];

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_overrides_path     = $config->overrides_path;
        $this->_overrides_template = $config->overrides_template;
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'overrides_path'     => sprintf('%s%2$seasydoclabs%2$stranslations%2$s%%s%2$s', WP_CONTENT_DIR, DIRECTORY_SEPARATOR),
            'overrides_template' => sprintf('<File>%s<File>.<Format>', DIRECTORY_SEPARATOR)
        ]);

        parent::_initialize($config);
    }

    /**
     * Locate the resource overrides based on a url
     *
     * @param  string $url  The resource url
     * @return string|false  The physical overrides path for the resource or FALSE if the url cannot be located
     */
    public function locateOverrides($url)
    {
        $result = false;

        if(!isset($this->__override_location_cache[$url]))
        {
            $info = $this->parseUrl($url);

            //Find the file
            foreach($this->getOverridesTemplates($url) as $template)
            {
                $path = str_replace(
                    array('<Package>'     , '<Path>'     ,'<File>'      , '<Format>'     , '<Type>'),
                    array($info['package'], $info['path'], $info['file'], $info['format'], $info['type']),
                    $template
                );

                if ($results = glob($path))
                {
                    foreach($results as $file)
                    {
                        if($result = $this->realPath($file)) {
                            break (2);
                        }
                    }
                }
            }

            $this->__override_location_cache[$url] = $result;
        }

        return $this->__override_location_cache[$url];
    }

    /**
     * Get the list of overrides templates
     *
     * @param  string $url The language url
     * @return array The overrides templates
     */
    public function getOverridesTemplates($url)
    {
        $template = $this->_overrides_template;

        $info = $this->parseUrl($url);
        $path = sprintf($this->_overrides_path, $info['package']);

        //Qualify relative path

        if(substr($template, 0, 1) !== '/')
        {
            if (!isset($info['domain']))
            {
                // Check for both base folder and root templates override folder

                $template = [
                    $path . $template,
                    $path . 'base' . DIRECTORY_SEPARATOR . $template
                ];
            }
            else $template = $path . $info['domain'] . DIRECTORY_SEPARATOR . $template;
        }

        return (array) $template;
    }
}
