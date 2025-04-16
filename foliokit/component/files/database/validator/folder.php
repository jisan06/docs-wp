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
 * Folder Validator Command
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class DatabaseValidatorFolder extends DatabaseValidatorNode
{
	protected function _beforeSave(Library\DatabaseContextInterface $context)
	{
        $result = parent::_beforeSave($context);

        if ($result)
        {
            $filter = $this->getObject('com:files.filter.folder.uploadable');
            $result = $filter->validate($context->getSubject());
            if ($result === false)
            {
                $errors = $filter->getErrors();
                if (count($errors)) {
                    $context->getSubject()->setStatusMessage(array_shift($errors));
                }
            }
        }

        return $result;
	}
}
