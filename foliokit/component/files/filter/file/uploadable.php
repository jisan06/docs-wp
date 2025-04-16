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
 * File Uploadable Filter
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class FilterFileUploadable extends Library\FilterChain
{
	public function __construct(Library\ObjectConfig $config)
	{
		parent::__construct($config);

		$this->addFilter($this->getObject('com:files.filter.file.name'), self::PRIORITY_HIGH);

		$this->addFilter($this->getObject('com:files.filter.file.extension'));
		$this->addFilter($this->getObject('com:files.filter.file.mimetype'));
		$this->addFilter($this->getObject('com:files.filter.file.size'));
	}
}
