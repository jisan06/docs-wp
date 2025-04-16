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
 * Database table for Wordpress users
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class DatabaseTableUsers extends Library\DatabaseTableAbstract
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'name'          => 'users',
            'column_map'    => [
                'name'          => 'display_name',
                'username'      => 'user_login',
                'email'         => 'user_email',
                'password'      => 'user_pass',
                'url'           => 'user_url',
                'registered_on' => 'user_registered',
                'activation'    => 'user_activation_key',
                'status'        => 'user_status',
            ],
        ]);

        parent::_initialize($config);
    }
}