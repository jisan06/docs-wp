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
 * File Name Filter
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class FilterFileName extends Library\FilterAbstract
{
    protected static $_rejected_names = ['.htaccess', 'web.config', 'index.htm', 'index.html', 'index.php', '.svn', '.git', 'cvs'];

    public function validate($entity)
	{
        $value = $this->sanitize($entity->name);

        if (in_array(strtolower($value), self::$_rejected_names))
        {
            throw new Library\ControllerExceptionActionFailed($this->getObject('translator')->translate(
                'You cannot upload a file named {filename} for security reasons.',
                ['filename' => $value]
            ));
        }

		if ($value == '') {
            return $this->_error($this->getObject('translator')->translate('Invalid file name'));
		}

        return true;
	}

	public function sanitize($value)
	{
		return $this->getObject('com:files.filter.path')->sanitize($value);
	}
}
