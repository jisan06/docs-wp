<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Files;

use EasyDocLabs\Library;

/**
 * Player Template Helper
 *
 * @author  Rastin Mehr <https://github.com/rmdstudio>
 * @package Koowa\Component\Files
 */
class TemplateHelperPlayer extends Library\TemplateHelperAbstract
{
    protected static $_SUPPORTED_FORMATS = [
        'audio' => ['aac', 'mp3', 'ogg', 'flac','x-flac', 'wave', 'wav', 'x-wav', 'x-pn-wav'],
        'video' => ['mp4', 'webm', 'ogg']
    ];

    public function load($config)
    {
        $config = new Library\ObjectConfig($config);

        $config->append(['selector' => '.easydoc_player']);

        static $imported = false;

        $html = '';

        if (!$imported)
        {
            $html = $this->getObject('com:files.view.player.html')
                ->getTemplate()
                ->render('com:files/player/default.html', ['selector' => $config->selector]);

            $imported = true;
        }

        return $html;
    }
}