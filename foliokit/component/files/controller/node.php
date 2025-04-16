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
 * Node Controller
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ControllerNode extends ControllerAbstract
{
	protected function _initialize(Library\ObjectConfig $config)
	{
		$config->append([
			'behaviors' => ['thumbnailable']
        ]);

		parent::_initialize($config);
	}

    protected function _beforeMove(Library\ControllerContextInterface $context)
    {
        $request = $this->getRequest();

        if ($request->data->has('name')) {
            $request->query->name = $request->data->name;
            unset($request->data->name);
        }
    }

    protected function _beforeCopy(Library\ControllerContextInterface $context)
    {
        $this->_beforeMove($context);
    }
}
