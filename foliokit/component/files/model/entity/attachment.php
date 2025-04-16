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
 * Attachment Model Entity
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ModelEntityAttachment extends Library\ModelEntityRow
{
    /**
     * Attachment file getter.
     *
     * @return Library\ModelEntityInterface
     */
    public function getPropertyFile()
    {
        return $this->getObject('com:files.model.files')
                    ->container($this->container_slug)
                    ->name($this->name)
                    ->thumbnails(true)
                    ->fetch()
                    ->getIterator()
                    ->current();
    }

    public function delete()
    {
        if ($result = parent::delete())
        {
            $file = $this->file;

            if (!$file->isNew()) $file->delete();
        }

        return $result;
    }

    public function toArray()
    {
        $data = parent::toArray();

        $file = $this->file;

        if (!$file->isNew()) {
            $data['file']      = $file->toArray();
        }

        $data['created_on_timestamp']  = strtotime($this->created_on);
        $data['attached_on_timestamp'] = strtotime($this->attached_on);

        return $data;
    }
}