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
 * Iterator Local Adapter
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class AdapterIterator extends Library\ObjectAbstract
{
	public function getFiles(array $config = [])
	{
		$config['type'] = 'files';
		return self::getNodes($config);
	}

	public function getFolders(array $config = [])
	{
		$config['type'] = 'folders';
		return self::getNodes($config);
	}

	public function getNodes(array $config = [])
	{
		$config['path'] = $this->getObject('com:files.adapter.folder',
					['path' => $config['path']])->getRealPath();

		try {
			$results = IteratorDirectory::getNodes($config);
		} catch (\Exception $e) {
			return false;
		}

		return $results;
	}
}
