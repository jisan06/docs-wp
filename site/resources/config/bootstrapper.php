<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

return [
    'aliases'     => [
        'com://site/easydoc.controller.toolbar.category' => 'com://admin/easydoc.controller.toolbar.category',
        'com://site/easydoc.controller.upload'           => 'com://admin/easydoc.controller.upload',
        'com://site/easydoc.controller.tag'              => 'com://admin/easydoc.controller.tag',
        'com://site/easydoc.controller.file'             => 'com://admin/easydoc.controller.file'
    ],

    'identifiers' => [
        'com://site/easydoc.model.documents'  => [
            'state' => 'com://site/easydoc.model.state'
        ],
        'event.subscriber.factory'           => [
            'subscribers' => [
                'com://site/easydoc.event.subscriber.notfound'
            ]
        ],
        'com://site/easydoc.model.categories' => [
            'state' => 'com://site/easydoc.model.state'
        ]
    ]
];
