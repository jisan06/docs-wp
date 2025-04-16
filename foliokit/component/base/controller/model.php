<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Library;

/**
 * Controller Model
 *
 * @author  Israel Canasa <http://github.com/raeldc>
 * @package EasyDocLabs\Component\Base
 */
abstract class ControllerModel extends Library\ControllerModel
{
    /**
     * Initializes the default configuration for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param  Library\ObjectConfig $config Configuration options
     * @return void
     */
    protected function _initialize(Library\ObjectConfig $config)
    {
        $toolbars = [];

        if($this->getIdentifier()->domain === 'admin') {
            $toolbars[] = 'menubar';
        }

        $config->append([
            'toolbars' => $toolbars,
            'behaviors' => ['editable', 'persistable'],
        ]);

        parent::_initialize($config);
    }
}
