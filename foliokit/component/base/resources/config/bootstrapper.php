<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

return [
    'aliases'  => [

        'request'             => 'com:base.dispatcher.request',
        'response'            => 'com:base.dispatcher.response',
        'dispatcher.request'  => 'com:base.dispatcher.request',
        'dispatcher.response' => 'com:base.dispatcher.response',
        'dispatcher.router'   => 'com:base.dispatcher.router',
        'translator'          => 'com:base.translator',
        'user'                => 'com:base.user',
        'user.session'        => 'com:base.user.session',
        'event.publisher'     => 'com:base.event.publisher',
        'exception.handler'   => 'com:base.exception.handler',
        'date'                => 'com:base.date',
        'block'               => 'com:base.block.default',
        'dispatcher.page'     => 'com:base.dispatcher.page',
        'license'             => 'com:base.license',

        'lib:database.driver.mysqli'       => 'com:base.database.driver.mysqli',
        'lib:dispatcher.router.route'      => 'com:base.dispatcher.router.route',
        'lib:filesystem.stream.buffer'     => 'com:base.filesystem.stream.buffer',
        'lib:template.locator.component'   => 'com:base.template.locator.component',
        'lib:object.locator.external'      => 'com:base.object.locator.external',
        //'lib:template.locator.file'        => 'com:base.template.locator.file',
        'lib:translator.locator.component' => 'com:base.translator.locator.component',
        //'lib:translator.locator.file'      => 'com:base.translator.locator.file',
    ],
    'identifiers' => [

        'event.subscriber.factory' => [
            'subscribers' => [
                'com:base.event.subscriber.redirect',
                'com:base.event.subscriber.message',
                'com:base.event.subscriber.exception',
            ]
        ],

        'com:scheduler.controller.dispatcher' => [
            'jobs' => [
                'com:base.job.license'
            ]
        ],

        'user.provider'  => [
            'model' => 'com:base.model.users',
        ],

        'user.session' => [
            'handler' => 'native',
            'name' => false,
        ]
    ]
];
