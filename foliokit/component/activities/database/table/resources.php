<?php
/**
 * Foliokit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Activities;

use EasyDocLabs\Library;

/**
 * Resources Database Table.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Activities
 */
class DatabaseTableResources extends Library\DatabaseTableAbstract
{
    /**
     * Initializes the options for the object.
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param Library\ObjectConfig $config Configuration options.
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'name'      => 'activities_resources',
            'behaviors' => [
                'com:activities.database.behavior.resources.creatable',
                'identifiable',
                'parameterizable' => ['column' => 'data']
            ],
            'filters'   => [
                'data' => 'json'
            ]
        ]);

        parent::_initialize($config);
    }
}
