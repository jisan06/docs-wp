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
 * File Extension Filter
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class FilterFileExtension extends Library\FilterAbstract
{
    public function validate($entity)
	{
	    if ($container = $entity->getContainer())
        {
            $allowed = Library\ObjectConfig::unbox($entity->getContainer()->getParameters()->allowed_extensions);

            if (is_array($allowed))
            {
                $allowed = array_map(function ($value) {
                    return strtolower($value);
                }, $allowed);

                $value = strtolower($entity->extension);

                if (!in_array($value, $allowed)) {
                    return $this->_error($this->getObject('translator')->translate('Invalid file extension'));
                }
            }
        }
	}
}
