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
 * Abstract Exporter Class.
 */
abstract class MigratorExportAbstract extends MigratorAbstract
{
    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   Library\ObjectConfig $config Configuration options.
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(array(
            'behaviors' => array('com:migrator.migrator.behavior.export.database'),
            'folder'    => ''
        ));

        parent::_initialize($config);
    }

    /**
     * Adds a job to the queue.
     *
     * @param      string $name   The job name.
     * @param      mixed  $config The job parameters.
     *
     * @return $this
     */
    public function addJob($name, $config)
    {
        $config = new Library\ObjectConfig($config);
        $config->append(array(
            'action'    => 'database',
            'chunkable' => true,
            'folder'    => $this->getConfig()->folder
        ));

        return parent::addJob($name, $config);
    }
}