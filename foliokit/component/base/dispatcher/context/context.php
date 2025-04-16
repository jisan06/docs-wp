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

class DispatcherContext extends Library\DispatcherContext implements Library\DispatcherContextInterface
{
    public function getRouter()
    {
        return Library\ObjectConfig::get('router');
    }

    public function setRouter(DispatcherRouterInterface $router)
    {
        return Library\ObjectConfig::set('router', $router);
    }
}