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
 * Event Publisher Profiler
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Event\Publisher
 */
class EventPublisherProfiler extends ObjectDecorator implements EventPublisherInterface
{
    /**
     * Enabled status of the profiler
     *
     * @var boolean
     */
    private $__enabled;

    /**
     * Array of profile marks
     *
     * @var array
     */
    private $__profiles;

    /**
     * Publish an event by calling all listeners that have registered to receive it.
     *
     * @param  string|EventInterface  $event     The event name or a EventInterface object
     * @param  array|Traversable       $attributes An associative array or a Traversable object
     * @param  ObjectInterface        $target    The event target
     * @return null|EventInterface Returns the event object. If the chain is not enabled will return NULL.
     */
    public function publishEvent($event, $attributes = array(), $target = null)
    {
        if ($this->isEnabled())
        {
            //Make sure we have an event object
            if (!$event instanceof EventInterface) {
                $event = new Event($event, $attributes, $target);
            }

            //Notify the listeners
            $listeners = $this->getListeners($event->getName());

            foreach ($listeners as $listener)
            {
                $start = microtime(true);

                call_user_func($listener, $event, $this);

                $this->__profiles[] = array(
                    'message'  => $event->getName(),
                    'period'   => microtime(true) - $start,
                    'time'     => microtime(true),
                    'memory'   => $this->getMemoryUsage(),
                    'target'   => $target instanceof ObjectInterface ? $target->getIdentifier() : $target,
                    'listener' => $listener
                );

                if (!$event->canPropagate()) {
                    break;
                }
            }

            return $event;
        }
        else $this->getDelegate()->publishEvent($event, $attributes, $target);

        return null;
    }

    /**
     * Add an event listener
     *
     * @param string|EventInterface  $event     The event name or a EventInterface object
     * @param callable                $listener  The listener
     * @param integer                 $priority  The event priority, usually between 1 (high priority) and 5 (lowest),
     *                                            default is 3 (normal)
     * @throws \InvalidArgumentException If the listener is not a callable
     * @throws \InvalidArgumentException  If the event is not a string or does not implement the EventInterface
     * @return EventPublisherProfiler
     */
    public function addListener($event, $listener, $priority = EventInterface::PRIORITY_NORMAL)
    {
        $this->getDelegate()->addListener($event, $listener, $priority);
        return $this;
    }

    /**
     * Remove an event listener
     *
     * @param string|EventInterface  $event     The event name or a EventInterface object
     * @param callable                $listener  The listener
     * @throws \InvalidArgumentException If the listener is not a callable
     * @throws \InvalidArgumentException  If the event is not a string or does not implement the EventInterface
     * @return EventPublisherProfiler
     */
    public function removeListener($event, $listener)
    {
        $this->getDelegate()->removeListener($event, $listener);
        return $this;
    }

    /**
     * Get a list of listeners for a specific event
     *
     * @param string|EventInterface  $event     The event name or a EventInterface object
     * @throws \InvalidArgumentException  If the event is not a string or does not implement the EventInterface
     * @return array An array containing the listeners ordered by priority
     */
    public function getListeners($event)
    {
        return $this->getDelegate()->getListeners($event);
    }

    /**
     * Set the priority of a listener
     *
     * @param  string|EventInterface  $event     The event name or a EventInterface object
     * @param  callable                $listener  The listener
     * @param  integer                 $priority  The event priority
     * @throws \InvalidArgumentException If the listener is not a callable
     * @throws \InvalidArgumentException If the event is not a string or does not implement the EventInterface
     * @return EventPublisherProfiler
     */
    public function setListenerPriority($event, $listener, $priority)
    {
        $this->getDelegate()->setListenerPriority($event, $listener, $priority);
        return $this;
    }

    /**
     * Get the priority of an event
     *
     * @param string|EventInterface  $event     The event name or a EventInterface object
     * @param callable                $listener  The listener
     * @throws \InvalidArgumentException If the listener is not a callable
     * @throws \InvalidArgumentException  If the event is not a string or does not implement the EventInterface
     * @return integer|false The event priority or FALSE if the event isn't listened for.
     */
    public function getListenerPriority($event, $listener)
    {
        return $this->getDelegate()->getListenerPriority($event, $listener);
    }

    /**
     * Get the list of event profiles
     *
     * @return array Array of event profiles
     */
    public function getProfiles()
    {
        return $this->__profiles;
    }

	/**
     * Get information about current memory usage.
     *
     * @return int The memory usage
     * @link PHP_MANUAL#memory_get_usage
     */
    public function getMemoryUsage()
    {
        $size = memory_get_usage(true);
        $unit = array('b','kb','mb','gb','tb','pb');

        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }

    /**
     * Returns information about a listener
     *
     * @param callable $listener  The listener
     * @return array Information about the listener
     */
    public function getListenerInfo($listener)
    {
        $info = array();

        if(is_callable($listener))
        {
            if (get_class('Closure') && $listener instanceof \Closure)
            {
                $info += array(
                    'type'   => 'Closure',
                    'pretty' => 'closure'
                );
            }

            if (is_string($listener))
            {
                try
                {
                    $r = new \ReflectionFunction($listener);
                    $file = $r->getFileName();
                    $line = $r->getStartLine();
                }
                catch (\ReflectionException $e)
                {
                    $file = null;
                    $line = null;
                }

                $info += array
                (
                    'type'  => 'Function',
                    'function' => $listener,
                    'file'  => $file,
                    'line'  => $line,
                    'pretty' => $listener,
                );
            }

            if (is_array($listener) || (is_object($listener)))
            {
                if (!is_array($listener)) {
                    $listener = array($listener, '__invoke');
                }

                $class = is_object($listener[0]) ? get_class($listener[0]) : $listener[0];

                try
                {
                    $r = new \ReflectionMethod($class, $listener[1]);
                    $file = $r->getFileName();
                    $line = $r->getStartLine();
                }
                catch (\ReflectionException $e)
                {
                    $file = null;
                    $line = null;
                }

                $info += array
                (
                    'type'   => 'Method',
                    'class'  => $class,
                    'method' => $listener[1],
                    'file'   => $file,
                    'line'   => $line,
                    'pretty' => $class.'::'.$listener[1],
                );
            }
        }

        return $info;
    }

    /**
     * Enable the profiler
     *
     * @return  EventPublisherProfiler
     */
    public function setEnabled($enabled)
    {
        $this->__enabled = (bool) $enabled;
        return $this;
    }

    /**
     * Check of the event profiler is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->__enabled;
    }

    /**
     * Set the decorated event publisher
     *
     * @param   EventPublisherInterface $delegate The decorated event publisher
     * @return  EventPublisherProfiler
     * @throws  \InvalidArgumentException If the delegate is not an event publisher
     */
    public function setDelegate($delegate)
    {
        if (!$delegate instanceof EventPublisherInterface) {
            throw new \InvalidArgumentException('Delegate: '.get_class($delegate).' does not implement EventPublisherInterface');
        }

        return parent::setDelegate($delegate);
    }

    /**
     * Get the decorated event publisher
     *
     * @return EventPublisherInterface
     */
    public function getDelegate()
    {
        return parent::getDelegate();
    }
}