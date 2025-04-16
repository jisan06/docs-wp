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
 * Event Publisher Singleton
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Event\Publisher
 */
class EventPublisher extends EventPublisherAbstract implements ObjectSingleton
{
    /**
     * Constructor.
     *
     * @param ObjectConfig $config  An optional ObjectConfig object with configuration options
     */
    public function __construct(ObjectConfig $config)
    {
        parent::__construct($config);

        $this->getObject('exception.handler')->addExceptionCallback(array($this, 'publishException'));
    }

    /**
     * Publish an event by calling all listeners that have registered to receive it.
     *
     * Function will avoid a recursive loop when an exception is thrown during even publishing and output a generic
     * exception instead.
     *
     * @param  \Throwable           $exception  The exception to be published.
     * @return  null|EventInterface
     */
    public function publishException(\Throwable $exception)
    {
        return parent::publishEvent('onException', ['exception' => $exception]);
    }

}
