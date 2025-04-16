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
 * Filesize Template Helper
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class TemplateHelperFilesize extends Library\TemplateHelperAbstract
{
	public function humanize($config = [])
	{
		$config = new Library\ObjectConfigJson($config);
		$config->append([
			'sizes' => ['B', 'KB', 'MB', 'GB', 'TB', 'PB']
        ]);
		$bytes = $config->size;
		$result = '';
		$format = (($bytes > 1024*1024 && $bytes % 1024 !== 0) ? '%.2f' : '%d').' %s';

		foreach ($config->sizes as $s)
		{
			$size = $s;
			if ($bytes < 1024) {
				$result = $bytes;
				break;
			}
			$bytes /= 1024;
		}

		return sprintf($format, $result, $this->getObject('translator')->translate($size));
	}
}
