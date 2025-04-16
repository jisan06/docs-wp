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
 * Containers Database Table
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class DatabaseTableContainers extends Library\DatabaseTableAbstract
{
	protected function _initialize(Library\ObjectConfig $config)
	{
		$config->append([
			'filters' => [
				'slug' 				 => 'cmd',
				'path'               => 'com:files.filter.path',
				'parameters'         => 'json'
            ],
			'behaviors' => [
                'lib:database.behavior.sluggable' => ['columns' => ['id', 'title']],
                'parameterizable'
            ]
        ]);

		parent::_initialize($config);
	}
}
