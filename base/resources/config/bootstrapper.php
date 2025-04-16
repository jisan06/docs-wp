<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

$config = [
    'aliases' => [
        'connect'                        => 'com:easydoc.controller.connect',
        'easydoc.users'                   => 'com:easydoc.database.table.users',
        'lib:database.query.select'      => 'com:easydoc.database.query.select',
        'com:base.database.query.select' => 'com:easydoc.database.query.select'
    ],
    'identifiers' => [
        'com:easydoc.view.behavior.notifiable' => [
            'notifiers' => [
                'com:easydoc.notifier.email'
            ]
        ],
        'com:base.dispatcher.page' => [
            'behaviors' => [
                'com:easydoc.dispatcher.behavior.licensable'
            ],
            'blocks' => [
                'com:easydoc.block.attachments',
                'com:easydoc.block.list',
                'com:easydoc.block.tree',
                'com:easydoc.block.flat',
                'com:easydoc.block.submit',
                'com:easydoc.block.search'
            ],
            'endpoints' => [
                '~documents' => [
                    'route' => 'component=easydoc',
                    'title' => 'Documents',
                ],
            ]
        ],
        'com:scheduler.controller.dispatcher' => [
            'jobs' => [
                'com:easydoc.job.license',
                'com:easydoc.job.multidownload',
                'com:easydoc.job.scans',
                'com:easydoc.job.emails',
                'com:easydoc.job.documents',
                'com:easydoc.job.categories'
            ]
        ],
    ]
];

// Enable group joins on multi-site environments for proper per site user filtering

if (is_multisite()) {
    $config['identifiers']['com:base.model.users'] = ['behaviors' => ['com:easydoc.model.behavior.groupable']];
}

return $config;
