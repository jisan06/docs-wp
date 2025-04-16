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
 * Event Subscriber Interface
 *
 * An EventSusbcriber knows himself what events he is interested in. Classes implementing this interface may be adding
 * listeners to an EventDispatcher through the {@link subscribe()} method.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Event\Subscriber
 */
interface EventSubscriberInterface
{
    /**
     * Priority levels
     */
    const PRIORITY_HIGHEST = 1;
    const PRIORITY_HIGH    = 2;
    const PRIORITY_NORMAL  = 3;
    const PRIORITY_LOW     = 4;
    const PRIORITY_LOWEST  = 5;

    /**
     * Register one or more listeners
     *
     * @param EventPublisherInterface $publisher
     * @@return array An array of public methods that have been attached
     */
    public function subscribe(EventPublisherInterface $publisher);

    /**
     * Unsubscribe all previously registered listeners
     *
     * @param EventPublisherInterface $publisher The event dispatcher
     * @return void
     */
    public function unsubscribe(EventPublisherInterface $publisher);

    /**
     * Check if the subscriber is already subscribed to the dispatcher
     *
     * @param  EventPublisherInterface $publisher  The event dispatcher
     * @return boolean TRUE if the subscriber is already subscribed to the dispatcher. FALSE otherwise.
     */
    public function isSubscribed(EventPublisherInterface $publisher);

    /**
     * Get the event listeners
     *
     * @return array
     */
    public static function getEventListeners();

    /**
     * Get the priority of the subscriber
     *
     * @return	integer The subscriber priority
     */
    public function getPriority();
}
