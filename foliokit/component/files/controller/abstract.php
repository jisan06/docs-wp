<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Files;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

/**
 * Default Controller
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
abstract class ControllerAbstract extends Base\ControllerModel
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'formats'   => ['json']
        ]);

        parent::_initialize($config);
    }

	public function getRequest()
	{
		$request = parent::getRequest();

		// "e_name" is needed to be compatible with com_content of Joomla
		if ($request->query->e_name) {
			$request->query->editor = $request->query->e_name;
		}
		
		return $request;
	}

	protected function _actionCopy(Library\ControllerContextInterface $context)
	{
		$entities = $this->getModel()->fetch();

		if(!$entities->isNew())
		{
            foreach($entities as $entity) {
                $entity->setProperties($context->request->data->toArray());
            }

			//Only throw an error if the action explicitly failed.
			if($entities->copy() === false)
			{
				$error = $entities->getStatusMessage();
				throw new Library\ControllerExceptionActionFailed($error ? $error : 'Copy Action Failed');
			}
			else $context->status = $entities->getStatus() === Library\Database::STATUS_CREATED ? Library\HttpResponse::CREATED : Library\HttpResponse::NO_CONTENT;
		}
		else throw new Library\ControllerExceptionResourceNotFound('Resource Not Found');

		return $entities;
	}

	protected function _actionMove(Library\ControllerContextInterface $context)
	{
		$entities = $this->getModel()->fetch();

		if(!$entities->isNew())
		{
            foreach($entities as $entity) {
                $entity->setProperties($context->request->data->toArray());
            }

			//Only throw an error if the action explicitly failed.
			if($entities->move() === false)
			{
				$error = $entities->getStatusMessage();
				throw new Library\ControllerExceptionActionFailed($error ? $error : 'Move Action Failed');
			}
			else $context->status = $entities->getStatus() === Library\Database::STATUS_CREATED ? Library\HttpResponse::CREATED : Library\HttpResponse::NO_CONTENT;
		}
		else throw new Library\ControllerExceptionResourceNotFound('Resource Not Found');

		return $entities;
	}
}
