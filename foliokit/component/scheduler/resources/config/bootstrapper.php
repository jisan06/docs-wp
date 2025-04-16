<?php
/**
 * FolioKit Scheduler
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

return [
    'identifiers' => [
        'com:base.dispatcher.page' => [
            'behaviors' => [
                'com:scheduler.dispatcher.behavior.schedulable'
            ],
        ]
    ]
];