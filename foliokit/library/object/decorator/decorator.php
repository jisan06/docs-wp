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
 * Object Decorator
 *
 * The object decorator implements the same interface as ObjectAbstract and can only be used to decorate objects extending from
 * ObjectAbstract. To decorate an object that does not extend from ObjectAbstract use ObjectDecoratorAbstract instead.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Object\Decorator
 */
abstract class ObjectDecorator extends ObjectDecoratorAbstract implements ObjectInterface, ObjectMixable, ObjectDecoratable
{
    /**
     * Checks if the decorated object or one of it's mixin's inherits from a class.
     *
     * @param   string|object $class  The class to check
     * @return  boolean  Returns TRUE if the object inherits from the class
     */
    public function inherits($class)
    {
        $delegate = $this->getDelegate();

        if ($delegate instanceof ObjectMixable) {
            $result = $delegate->inherits($class);
        } else {
            $result = $delegate instanceof $class;
        }

        return $result;
    }

    /**
     * Mixin an object
     *
     * When using mixin(), the calling object inherits the methods of the mixed in objects, in a LIFO order.
     *
     * @param   mixed $mixin   A ObjectIdentifier, identifier string or object implementing ObjectMixinInterface
     * @param   array $config  An optional associative array of configuration options
     * @return  ObjectDecorator
     */
    public function mixin($mixin, $config = array())
    {
        $this->getDelegate()->mixin($mixin, $config);
        return $this;
    }

    /**
     * Decorate the object
     *
     * When using decorate(), the decorator will be re-decorated. The decorator needs to extend from
     * ObjectDecorator.
     *
     * @param   mixed $decorator An ObjectIdentifier, identifier string or object implementing ObjectDecorator
     * @param   array $config    An optional associative array of configuration options
     * @return  ObjectDecoratorInterface
     * @throws  ObjectExceptionInvalidIdentifier If the identifier is not valid
     * @throws  \UnexpectedValueException If the decorator does not extend from ObjectDecorator
     */
    public function decorate($decorator, $config = array())
    {
        $decorator = $this->getDelegate()->decorate($decorator, $config);

        //Notify the decorator and set the delegate
        $decorator->onDecorate($this);

        return $decorator;
    }

    /**
     * Set the decorated object
     *
     * @param   ObjectInterface $delegate The object to decorate
     * @return  ObjectDecorator
     * @throws  \InvalidArgumentException If the delegate does not extend from ObjectInterface
     */
    public function setDelegate($delegate)
    {
        if (!$delegate instanceof ObjectInterface) {
            throw new \InvalidArgumentException('Delegate needs to implement ObjectInterface');
        }

        return parent::setDelegate($delegate);
    }

    /**
     * Get an instance of a class based on a class identifier only creating it if it does not exist yet.
     *
     * @param  mixed $identifier An ObjectIdentifier, identifier string or object implementing ObjectInterface
     * @param  array $config     An optional associative array of configuration settings.
     * @return	Object Return object on success, throws exception on failure
     */
    public function getObject($identifier, array $config = array())
    {
        return $this->getDelegate()->getObject($identifier, $config);
    }

    /**
     * Get an object identifier.
     *
     * @param  mixed $identifier An ObjectIdentifier, identifier string or object implementing ObjectInterface
     * @return ObjectIdentifier
     */
    public function getIdentifier($identifier = null)
    {
        return $this->getDelegate()->getIdentifier($identifier);
    }

    /**
     * Get the object configuration
     *
     * @param  mixed $identifier An ObjectIdentifier, identifier string or object implementing ObjectInterface
     * @return ObjectConfig
     */
    public function getConfig($identifier = null)
    {
        return $this->getDelegate()->getConfig($identifier);
    }
}
