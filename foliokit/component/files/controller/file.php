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
 * File Controller
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ControllerFile extends ControllerAbstract
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->addCommandCallback('before.add' , '_setFile');
        $this->addCommandCallback('before.edit', '_setFile');
    }

    protected function _initialize(Library\ObjectConfig $config)
	{
		$config->append([
			'behaviors' => ['chunkable', 'thumbnailable']
        ]);

		parent::_initialize($config);
	}

	protected function _setFile(Library\ControllerContextInterface $context)
	{
		if (empty($context->request->data->file) && $context->request->files->has('file'))
		{
			$context->request->data->file = $context->request->files->file['tmp_name'];
			if (empty($context->request->data->name)) {
				$context->request->data->name = $context->request->files->file['name'];
			}
		}
	}

    public function getRequest()
    {
        $request = parent::getRequest();

        // This is used to circumvent the URL size exceeding 2k bytes problem for file counts in uploader
        if ($request->query->view === 'files' && $request->data->has('name')) {
            $request->query->name = $request->data->name;
        }

        // This is used in Plupload to set the folder in the request payload instead of the URL
        if (!$request->query->has('folder') && $request->data->has('folder')) {
            $request->query->folder = $request->data->folder;
        }

        return $request;
    }

    protected function _actionRender(Library\ControllerContext $context)
    {
        $model  = $this->getModel();
        $result = null;

        if ($this->getRequest()->getFormat() === 'html')
        {
            // Serve file
            if ($model->getState()->isUnique())
            {
                $file = $this->getModel()->fetch();

                try
                {
                    $this->getResponse()
                        ->attachTransport('stream')
                        ->setContent($file->fullpath, $file->mimetype ?: 'application/octet-stream');
                }
                catch (\InvalidArgumentException $e) {
                    throw new Library\ControllerExceptionResourceNotFound('File not found');
                }
            }
            else $result = parent::_actionRender($context);
        }
        else $result = parent::_actionRender($context);

        return $result;
    }
}
