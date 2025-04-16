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
use EasyDocLabs\WP;

/**
 * Dispatcher Router Singleton
 *
 * Force the router object to a singleton with identifier alias 'router'.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Dispatcher\Router
 */
class DispatcherRouterAdmin extends DispatcherRouterBase implements Library\ObjectSingleton
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        if (\EasyDocLabs\WP::is_admin()) {
            $this->getObject('manager')->registerAlias($this->getIdentifier(), 'router');
        }
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'resolvers' => [
                'admin',
            ]
        ]);

        parent::_initialize($config);
    }

    protected function _getBasePath($query)
    {
        $basepath =  $this->getObject('request')->getBasePath();

        if (!WP::is_admin())
        {
            $basepath = rtrim($basepath, '/');

            // Make sure we append wp-admin to the basepath

            $basepath .= '/wp-admin';
        }

        return $basepath . '/admin.php';
    }
}