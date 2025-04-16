<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/nooku/nooku-framework-wordpress for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Library;

class EventPublisher extends Library\EventPublisher
{
    /**
     * Wordpress actions indexed by event name
     * @var array
     */
    private $__publishers = [];

    /**
     * Registers Wordpress actions before publishing events
     *
     * {@inheritdoc}
     */
    public function publishEvent($event, $attributes = array(), $target = null)
    {
        // Only component-specific events are published as strings, the rest comes as Library\Event instances
        // See: \EasyDocLabs\Library\BehaviorEventable::execute
        if ($this->isEnabled() && is_string($event)) {
            $this->__addWordpressListeners($event);
        }

        return parent::publishEvent($event, $attributes, $target);
    }

    /**
     * Goes through registered Wordpress hooks and finds the ones for the current event name
     * @param $event
     */
    private function __addWordpressListeners($event)
    {
        $name = $event instanceof Library\Event ? $event->getName() : $event;

        if (!array_key_exists($name, $this->__publishers))
        {
            $filters = \EasyDocLabs\WP::global('wp_filter');

            if (isset($filters[$name]) && !empty($filters[$name]->callbacks))
            {
                foreach ($filters[$name]->callbacks as $priority => $callbacks)
                {
                    foreach ($callbacks as $action)
                    {
                        if (isset($action['function']) && is_callable($action['function']))
                        {
                            $priority = $priority > 5 ? 5 : ($priority < 1 ? 1 : $priority);
                            $this->__publishers[$name][] = $action['function'];

                            $this->addListener($name, $action['function'], $priority);
                        }
                    }
                }
            }
        }

    }
}