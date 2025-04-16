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
 * Foliokit Template Engine
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Template\Engine
 */
class TemplateEnginePhp extends TemplateEngineAbstract
{
    /**
     * The engine file types
     *
     * @var string
     */
    protected static $_file_types = array('php');

    /**
     * A list of compiled templates by source
     *
     * @var array
     */
    protected static $_buffers = [];

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
            'functions'           => array(
                'import' => array($this, 'renderPartial'),
            ),
        ));

        parent::_initialize($config);
    }

    public function __destruct()
    {
        try
        {
            foreach (static::$_buffers as $name => $buffer)
            {
                $path = $buffer->getPath();
                $buffer->close();
                
                // Remove the file if it exists - buffer://temp files are not automatically removed
                if (is_file($path)) {
                    @unlink($path);
                }

                unset(static::$_buffers[$name]);
            }
        }
        catch (\Exception $e) {}
    }

    /**
     * Render a template
     *
     * @param   string  $source   A fully qualified template url or content string
     * @param   array   $data     An associative array of data to be extracted in local template scope
     * @throws \InvalidArgumentException If the template could not be located
     * @throws \RuntimeException         If a partial template url could not be fully qualified
     * @throws \RuntimeException         If the template could not be loaded
     * @throws \RuntimeException         If the template could not be compiled

     * @return string The rendered template source
     */
    public function render($source, array $data = array())
    {
        $source = parent::render($source, $data);

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

        //Evaluate the template
        $result = $this->evaluateSource($cache_file);

        //Render the debug information
        $result = $this->renderDebug($result);

        return $result;
    }

        /**
     * Check if a file exists in the cache
     *
     * @param string $file The file name
     * @return string|false The cache file path. FALSE if the file cannot be found in the cache
     */
    public function isCached($file)
    {
        $result = parent::isCached($file);
        
        if ($result === false && isset(static::$_buffers[$file])) $result = static::$_buffers[$file]->getPath();

        return $result;
    }

    /**
     * Cache the compiled template source
     *
     * Write the template content to a file buffer. If cache is enabled the file will be buffer using cache settings
     * If caching is not enabled the file will be written to the temp path using a buffer://temp stream.
     *
     * @param  string $name     The file name
     * @param  string $source   The template source to cache
     * @throws \RuntimeException If the template cache path is not writable
     * @throws \RuntimeException If template cannot be cached
     * @return string The cached template file path
     */
    public function cacheSource($name, $source)
    {
        if(!$file = parent::cacheSource($name, $source))
        {
            static::$_buffers[$name] = $this->getObject('filesystem.stream.factory')->createStream('easydoc-buffer://temp', 'w+b');
            static::$_buffers[$name]->truncate(0);
            static::$_buffers[$name]->write($source);

            $file = static::$_buffers[$name]->getPath();
        }

        return $file;
    }

    /**
     * Compile the template source
     *
     * If the a compile error occurs and exception will be thrown if the error cannot be recovered from or if debug
     * is enabled.
     *
     * @param  string $source The template source to compile
     * @throws TemplateExceptionSyntaxError
     * @return string The compiled template content
     */
    public function compileSource($source)
    {
        //Convert PHP tags
        
        // convert "<?=" to "<?php echo"
        $find = '/\<\?\s*=\s*(.*?)/';
        $replace = "<?php echo \$1";
        $source = preg_replace($find, $replace, $source);

        // convert "<?" to "<?php"
        $find = '/\<\?(?:php)?\s*(.*?)/';
        $replace = "<?php \$1";
        $source = preg_replace($find, $replace, $source);


        //Get the template functions
        $functions = $this->getFunctions();

        //Compile to valid PHP
        $tokens   = token_get_all($source);

        $result = '';
        for ($i = 0; $i < sizeof($tokens); $i++)
        {
            if(is_array($tokens[$i]))
            {
                list($token, $content) = $tokens[$i];

                switch ($token)
                {
                    //Proxy registered functions through __call()
                    case T_EMPTY:
                    case T_STRING :

                        if(isset($functions[$content]) )
                        {
                            $prev = (array) $tokens[$i-1];
                            $next = (array) $tokens[$i+1];

                            if($next[0] == '(' && $prev[0] !== T_OBJECT_OPERATOR) {
                                $result .= '$this->'.$content;
                                break;
                            }
                        }

                        $result .= $content;
                        break;

                    //Do not allow to use $this context
                    case T_VARIABLE:

                        if ('$this' == $content) {
                            throw new TemplateExceptionSyntaxError('Using $this when not in object context');
                        }

                        $result .= $content;
                        break;

                    default:
                        $result .= $content;
                        break;
                }
            }
            else $result .= $tokens[$i] ;
        }

        return $result;
    }

    /**
     * Evaluate the template using a simple sandbox
     *
     * @param  string $path The template path
     * @throws \RuntimeException If the template could not be evaluated
     * @return string The evaluated template content
     */
    public function evaluateSource($path)
    {
        if (\function_exists('opcache_invalidate')) {
            @opcache_invalidate($path, true);
        }

        ob_start();

        extract($this->getData(), EXTR_SKIP);
        include $path;
        if(!$result = ob_get_clean()) {
            throw new \RuntimeException(sprintf('The template "%s" cannot be evaluated.', $path));
        }

        return trim($result);
    }
}