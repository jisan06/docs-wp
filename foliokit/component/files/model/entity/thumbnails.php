<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Files;

/**
 * Thumbnails Entity
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ModelEntityThumbnails extends ModelEntityFiles
{
    public function toArray()
    {
        $data = parent::toArray();

        if ($this->count() == 1) {
            $data = current($data); // Un-wrap the thumbnail;
        }

        return $data;
    }
}
