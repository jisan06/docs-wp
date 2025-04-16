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
 * File Validator Command
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class DatabaseValidatorFile extends DatabaseValidatorNode
{
	protected function _beforeSave(Library\DatabaseContextInterface $context)
	{
		$entity = $context->subject;

		if (is_string($entity->file) && !is_uploaded_file(str_replace(chr(0), '', $entity->file)))
		{
			// remote file
            $file = $this->getObject('com:files.model.entity.url');
            $file->setProperties(['file' => $entity->file]);

            if (!$file->contents) {
                throw new \RuntimeException('File cannot be downloaded');
            }

            $entity->contents = $file->contents;

			if (empty($entity->name))
			{
				$uri = $this->getObject('lib:http.url', ['url' => $entity->file]);
	        	$path = $uri->toString(Library\HttpUrl::PATH);
	        	if (strpos($path, '/') !== false) {
	        		$path = \Foliokit\basename($path);
	        	}

	        	$entity->name = $path;
			}
		}

        $result = parent::_beforeSave($context);

        if ($result)
        {
            $filter = $this->getObject('com:files.filter.file.uploadable');
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
