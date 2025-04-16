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
 * Abstract Object Locator
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Object\Locator
 */
abstract class ObjectLocatorAbstract extends ObjectAbstract implements ObjectLocatorInterface
{
    /**
     * The locator type
     *
     * @var string
     */
    protected static $_type = '';

    /**
     * Locator identifiers
     *
     * @var array
     */
    private $__identifiers = array();

    /**
     * Returns a fully qualified class name for a given identifier.
     *
     * @param ObjectIdentifier $identifier An identifier object
     * @param bool  $fallback   Use the fallback sequence to locate the identifier
     * @return string|false  Return the class name on success, returns FALSE on failure
     */
    final public function locate(ObjectIdentifier $identifier, $fallback = true)
    {
        $result = false;
        $missed = array();

        $info = $this->parseIdentifier($identifier);

        //Find the class
        foreach($this->getClassTemplates($identifier) as $template)
        {
            $class = str_replace(
                array('<Package>'     ,'<Path>'      ,'<File>'      , '<Class>'),
                array($info['package'], $info['path'], $info['file'], $info['class']),
                $template
            );

            //Do not try to locate a class twice
            if(!isset($missed[$class]) && class_exists($class))
            {
                $result = $class;
                break;
            }

            if(!$fallback) {
                break;
            }

            //Mark the class
            $missed[$class] = false;
        }

        return $result;
    }

    /**
     * Parse the identifier
     *
     * @param  ObjectIdentifier $identifier An object identifier
     * @return array
     */
    public function parseIdentifier(ObjectIdentifier $identifier)
    {
        $package = ucfirst($identifier->package);
        $path    = StringInflector::implode($identifier->path);
        $file    = ucfirst($identifier->name);
        $class   = $path.$file;

        $info = array(
            'class'      => $class,
            'package'    => $package,
            'path'       => $path,
            'file'       => $file,
        );

        return $info;
    }

    /**
     * Get the list of class templates for an identifier
     *
     * @param ObjectIdentifier $identifier The object identifier
     * @return array The class templates for the identifier
     */
    public function getClassTemplates(ObjectIdentifier $identifier)
    {
        $templates = array(
            __NAMESPACE__.'\<Package><Path><File>',
            __NAMESPACE__.'\<Package><Path>Default',
        );

        return $templates;
    }

    /**
     * Register an identifier
     *
     * @param  string       $identifier
     * @param  string|array $namespace(s) Sequence of fallback namespaces
     * @return ObjectLocatorAbstract
     */
    public function registerIdentifier($identifier, $namespaces)
    {
        $this->__identifiers[$identifier] = (array) $namespaces;
        return $this;
    }

    /**
     * Get the namespace(s) for the identifier
     *
     * @param string $identifier The package identifier
     * @return array|false The namespace(s) or FALSE if the identifier does not exist.
     */
    public function getIdentifierNamespaces($identifier)
    {
        return isset($this->__identifiers[$identifier]) ?  $this->__identifiers[$identifier] : false;
    }

    /**
     * Get the type
     *
     * @return string
     */
    public static function getType()
    {
        return static::$_type;
    }
}
