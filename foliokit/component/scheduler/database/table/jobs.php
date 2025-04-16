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
 * Jobs table
 *
 * @author Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Scheduler
 */
class DatabaseTableJobs extends Library\DatabaseTableAbstract
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors' => [
                'orderable',
                'lib:database.behavior.modifiable',
                'identifiable',
                'parameterizable' => ['column' => 'state']
            ],
            'filters' => [
                'state' => 'json'
            ]
        ]);

        parent::_initialize($config);
    }
}
