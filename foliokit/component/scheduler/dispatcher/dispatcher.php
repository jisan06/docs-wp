<?php
/**
 * FolioKit Scheduler
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */
namespace EasyDocLabs\Component\Scheduler;

use EasyDocLabs\Library;

/**
 * Schedulable behavior
 *
 * @author Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Scheduler
 */
class Dispatcher extends Library\DispatcherAbstract
{
    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   Library\ObjectConfig $config Configuration options.
     * @return  void
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'controller' => 'com:scheduler.controller.dispatcher',
            'debug'      => \Foliokit::isDebug()
        ]);

        parent::_initialize($config);
    }

    protected function _actionDispatch(Library\DispatcherContext $context)
    {
        // Ensure the URL is not indexed ("none" equals "noindex, nofollow")
        $context->getResponse()->getHeaders()->set('X-Robots-Tag', 'none');

        $job_dispatcher = $this->getController();

        $context = $job_dispatcher->getContext();

        $job_dispatcher->synchronize($context);

        $result  = null;
        $can_run = function($result, $context) use ($job_dispatcher) {
            static $i = 0, $time = 0.0;
            if ($i == 0 ||
                ($i < 5
                    && $job_dispatcher->getNextJob()
                    && ($result === JobInterface::JOB_SKIP || $time < 7.5))
            ) {
                $i++;
                $time += $context->getJobDuration();

                $context->log(sprintf('Current total time %f', $time));

                return true;
            }

            return false;
        };

        while ($can_run($result, $context)) {
            $result = $job_dispatcher->dispatch($context);
        }

        $response = [
            'continue' => (bool) $job_dispatcher->getNextJob(),
            'sleep_until' => $context->sleep_until,
            'logs'     => $this->getConfig()->debug ? $context->getLogs() : []
        ];

        $context->request->setFormat('json');
        $context->response->setContent(json_encode($response), 'application/json');
        $context->response->headers->set('Cache-Control', 'no-cache');

        $this->send();
    }
}