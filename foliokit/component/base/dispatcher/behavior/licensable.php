<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/nooku/nooku-framework-wordpress for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Library;

class DispatcherBehaviorLicensable extends Library\DispatcherBehaviorAbstract
{
    protected $_error_added = false;

    protected $_views;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_views = Library\ObjectConfig::unbox($config->views);
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['views' => []]);

        parent::_initialize($config);
    }

    protected function _beforeInit(Library\DispatcherContext $context)
    {
        $query = $this->getRequest()->getQuery();

        if ($query->component === 'foliokit' && $query->controller === 'license') {

            $dispatcher = $this->getObject('com:base.dispatcher', [
                'router'  => 'com:base.dispatcher.router.site'
            ]);

            $this->getObject('user')->setAuthentic();
            $dispatcher->setController('com:base.controller.license');
            $dispatcher->dispatch();
        }
    }

    protected function _beforeDispatch(Library\DispatcherContext $context)
    {
        $license = $this->getObject('license');
        $license->load();
    }
}
