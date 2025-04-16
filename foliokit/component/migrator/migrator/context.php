<?php
/**
 * @package     Foliokit Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Migrator;

use EasyDocLabs\Library;

/**
 * Job context
 *
 */
class MigratorContext extends Library\ControllerContext
{
    /**
     * @var Library\ObjectConfig
     */
    protected $_job;

    /**
     * @var string
     */
    protected $_error;

    /**
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * @param string $error
     * @return $this
     */
    public function setError($error)
    {
        $this->_error = $error;

        return $this;
    }

    /**
     * @return Library\ObjectConfig
     */
    public function getJob()
    {
        return $this->_job;
    }

    /**
     * @param Library\ObjectConfig $job
     * @return $this
     */
    public function setJob(Library\ObjectConfig $job)
    {
        $this->_job = $job;

        return $this;
    }
}