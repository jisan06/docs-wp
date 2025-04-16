<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Library;

/**
 * Asset Template Filter
 *
 * Filter allows to define asset url schemes that are replaced on render.
 *
 * A default assets:// scheme is added that is rewritten to '/assets/'.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Template\Filter
 */
class TemplateFilterAsset extends TemplateFilterAbstract
{
    /**
     * The schemes
     *
     * @var array
     */
    protected $_schemes = array();

    /**
     * Constructor.
     *
     * @param   ObjectConfig $config Configuration options
     */
    public function __construct(ObjectConfig $config)
    {
        parent::__construct($config);

        $schemes = ObjectConfig::unbox($config->schemes);
        foreach(array_reverse($schemes) as $alias => $path) {
            $this->addScheme($alias, $path);
        }
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   ObjectConfig $config Configuration options
     * @return  void
     */
    protected function _initialize(ObjectConfig $config)
    {
        $config->append(array(
            'schemes' => array('assets://' => '/assets/'),
            'priority' => self::PRIORITY_LOW,
        ));

        parent::_initialize($config);
    }

    /**
     * Add a url scheme
     *
     * @param string $alias  Scheme to be appended
     * @param mixed  $path   The path to replace the scheme
     * @param boolean $prepend Whether to prepend the autoloader or not
     * @return TemplateFilterAsset
     */
    public function addScheme($alias, $path, $prepend = false)
    {
        if($prepend) {
            $this->_schemes = array($alias => $path) + $this->_schemes;
        } else {
            $this->_schemes = $this->_schemes + array($alias => $path);
        }

        return $this;
    }

    /**
     * Get the schemes
     *
     * @return array
     */
    public function getSchemes()
    {
        return $this->_schemes;
    }

    /**
     * Convert the schemes to their real paths
     *
     * @param string $text  The text to parse
     * @param TemplateInterface $template A template object
     * @return void
     */
    public function filter(&$text, TemplateInterface $template)
    {
        if(!empty($this->_schemes))
        {
            $schemes = array();
            foreach($this->_schemes as $scheme => $path)
            {
                //Handle cyclical schemes
                if(!empty($schemes))
                {
                    $scheme = str_replace(
                        array_keys($schemes),
                        array_values($schemes),
                        $scheme);
                }

                $text = str_replace($scheme, $path, $text);
                $schemes[$scheme] = $path;
            }
        }
    }
}