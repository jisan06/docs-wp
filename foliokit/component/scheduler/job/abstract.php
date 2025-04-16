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
 * Job interface
 *
 * @author Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Scheduler
 */
abstract class JobAbstract extends Library\ObjectAbstract implements JobInterface
{
    /**
     * Prioritized flag
     *
     * @var bool
     */
    protected $_prioritized;

    /**
     * Job frequency
     *
     * @var int
     */
    protected $_frequency;

    /**
     * @param Library\ObjectConfig $config
     */
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->setPrioritized($config->prioritized);
        $this->setFrequency($config->frequency);
    }

    /**
     * @param Library\ObjectConfig $config
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'prioritized' => false,
            'frequency'   => JobInterface::FREQUENCY_HOURLY
        ]);
    }

    /**
     * Runs the job
     *
     * @param  JobContextInterface $context Context
     * @return int The result of $this->complete() or $this->suspend()
     */
    abstract public function run(JobContextInterface $context);

    /**
     * Signals the job completion
     *
     * @return int
     */
    public function complete()
    {
        return JobInterface::JOB_COMPLETE;
    }

    /**
     * Signals the job suspension
     *
     * @return int
     */
    public function suspend()
    {
        return JobInterface::JOB_SUSPEND;
    }

    /**
     * Signals an error in the job
     *
     * @return int
     */
    public function fail()
    {
        return JobInterface::JOB_FAIL;
    }

    /**
     * Signals that there is no need to run the job
     *
     * @return int
     */
    public function skip()
    {
        return JobInterface::JOB_SKIP;
    }

    /**
     * Returns the prioritized flag of the job
     *
     * @return bool
     */
    public function isPrioritized()
    {
        return $this->_prioritized;
    }

    /**
     * Sets if the job is prioritized
     * @param $prioritized bool
     * @return $this
     */
    public function setPrioritized($prioritized)
    {
        $this->_prioritized = (bool) $prioritized;

        return $this;
    }

    /**
     * Returns the job frequency in cron expression
     *
     * @return string
     */
    public function getFrequency()
    {
        return $this->_frequency;
    }

    /**
     * Sets the frequency
     *
     * @param int $frequency
     * @return $this
     */
    public function setFrequency($frequency)
    {
        $this->_frequency = $frequency;

        return $this;
    }
}