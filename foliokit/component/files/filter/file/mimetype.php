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
 * File Mimetype Filter
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class FilterFileMimetype extends Library\FilterAbstract
{
    public function validate($entity)
	{
	    if ($container = $entity->getContainer())
        {
            $mimetypes = Library\ObjectConfig::unbox($container->getParameters()->allowed_mimetypes);

            if (is_array($mimetypes))
            {
                $mimetype = $entity->mimetype;

                if (empty($mimetype))
                {
                    if ($entity->file instanceof \SplFileInfo) {
                        $mimetype = $this->getObject('com:files.mixin.mimetype')->getMimetype($entity->file->getPathname());
                    }
					elseif (is_uploaded_file(str_replace(chr(0), '', $entity->file))) {
                        $mimetype = $this->getObject('com:files.mixin.mimetype')->getMimetype($entity->file);
                    }
                }

                if ($mimetype && !in_array($mimetype, $mimetypes)) {
                    return $this->_error($this->getObject('translator')->translate('Invalid Mimetype'));
                }
            }
        }
	}
}
