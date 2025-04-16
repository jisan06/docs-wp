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
 * Restrictable Database Behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Base
 */
class DatabaseBehaviorRestrictable extends Library\DatabaseBehaviorAbstract
{
    protected $_actions;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_actions = Library\ObjectConfig::unbox($config->actions);
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['actions' => []]);
    
        parent::_initialize($config);
    }

    public function isRestrictedAction($action)
    {
        return in_array($action, $this->_actions);
    }
}