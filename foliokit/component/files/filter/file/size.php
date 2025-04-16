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
 * File Size Filter
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class FilterFileSize extends Library\FilterAbstract
{
    public function validate($entity)
	{
	    if ($container = $entity->getContainer())
        {
            $max = $container->getParameters()->maximum_size;

            if ($max)
            {
                $size = $entity->contents ? strlen($entity->contents) : false;

				if ($entity->file instanceof \SplFileInfo && $entity->file->isFile()) {
                    $size = $entity->file->getSize();
                }
				elseif (!$size && is_uploaded_file(str_replace(chr(0), '', $entity->file))) {
                    $size = filesize($entity->file);
                }

                if ($size && $size > $max) {
                    return $this->_error($this->getObject('translator')->translate('File is too big'));
                }
            }
        }
	}
}
