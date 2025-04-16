<?php
/**
 * FolioKit Tags
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Tags;

use EasyDocLabs\Library;

/**
 * Tags Database Table
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package Koowa\Component\Tags
 */
class DatabaseTableTags extends Library\DatabaseTableAbstract
{
    /**
     * Initializes the default configuration for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param Library\ObjectConfig $config 	An optional ObjectConfig object with configuration options.
     * @return void
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors'  => [
                'creatable', 'modifiable', 'lockable', 'sluggable',
            ]
        ]);

        parent::_initialize($config);
    }
}