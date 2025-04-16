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
 * Markdown Template Engine
 *
 * @link https://github.com/erusev/parsedown
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Template\Engine
 */
class TemplateEngineMarkdown extends TemplateEngineAbstract
{
    /**
     * Markdown compiler
     *
     * @var callable
     */
    private static $__compiler;

    /**
     * The engine file types
     *
     * @var string
     */
    protected static $_file_types = array('md', 'markdown');

    /**
     * Constructor
     *
     * @param ObjectConfig $config   An optional ObjectConfig object with configuration options
     */
    public function __construct(ObjectConfig $config)
    {
        parent::__construct($config);

        //Set the markdown compiler
        if($config->compiler) {
            $this->setCompiler($config->compiler);
        }
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param  ObjectConfig $config An optional ObjectConfig object with configuration options
     */
    protected function _initialize(ObjectConfig $config)
    {
        $config->append(array(
            'compiler' => null,
        ));

        parent::_initialize($config);
    }

    /**
     * Render a template
     *
     * @param   string  $source     The template url or content
     * @param   array   $data       An associative array of data to be extracted in local template scope
     * @return string The rendered template source
     */
    public function render($source, array $data = array())
    {
        parent::render($source, $data);

        //Load the template
        if($this->getObject('filter.path')->validate($source))
        {
            $source_file = $this->locateSource($source);

            if(!$cache_file = $this->isCached($source_file))
            {
                $source     = $this->loadSource($source_file);
                $source     = $this->compileSource($source);
                $cache_file = $this->cacheSource($source_file, $source);
            }
        }
        else
        {
            $name        = crc32($source);
            $source_file = '';

            if(!$cache_file = $this->isCached($name))
            {
                $source     = $this->compileSource($source);
                $cache_file = $this->cacheSource($name, $source);
            }
        }

        //Include the template
        $result = file_get_contents($cache_file);

        //Render the debug information
        return  $this->renderDebug($result);
    }

    /**
     * Compile the template
     *
     * @param   string  $source The template source to compile
     * @throws \RuntimeException If the template could not be compiled
     * @return string|false The compiled template content or FALSE on failure.
     */
    public function compileSource($source)
    {
        $result = false;

        $compiler = $this->getCompiler();
        if(is_callable($compiler)) {
            $result = call_user_func($compiler, $source);
        }

        if(!is_string($result)) {
            throw new \RuntimeException(sprintf('The template content cannot be compiled.'));
        }

        return $result;
    }

    /**
     * Get callback for compiling markdown
     *
     * @return callable
     */
    public function getCompiler()
    {
        return static::$__compiler;
    }

    /**
     * Set callback for compiling markdown
     *
     * @param  callable $compiler the compiler to set
     * @return TemplateEngineMarkdown
     */
    public function setCompiler(callable $compiler)
    {
        static::$__compiler = $compiler;
        return $this;
    }
}
