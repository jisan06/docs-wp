<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class DatabaseTableEmails extends Library\DatabaseTableAbstract
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors'  => [
                'creatable',
                'parameterizable'
            ],
            'filters'    => [
                'body'       => ['html'],
                'parameters' => ['json']
            ],
            'column_map' => [
                'notification' => 'easydoc_notification_id'
            ]
        ]);

        parent::_initialize($config);
    }
}