<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;

class ControllerDownload extends ControllerDocument
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors' => ['redirectable', 'previewable', 'compressible', 'com:easydoc.controller.behavior.notifiable'],
            'model' => 'documents',
            'view'  => 'download'
        ]);

        parent::_initialize($config);
    }

    protected function _beforeDownload(Library\ControllerContext $context)
    {
        $context->setEntity($this->getModel()->fetch());

        if ($this->isDispatched())
        {
            if ($this->getOptions()->force_download) {
                $request = $this->getRequest();
                $request->query->set('force-download', 1);
            }
        }
    }

    protected function _actionRender(Library\ControllerContext $context)
    {
        $this->download($context);
    }

    protected function _actionDownload(Library\ControllerContext $context)
    {
        $document = $context->getEntity();

        if (!$document->isNew())
        {
            $schemes = $document->getSchemes(); //Get the schemes whitelist

            if(isset($schemes[$document->storage->scheme]) && $schemes[$document->storage->scheme] === true)
            {
                //Set mimetype
                $file = $document->storage;

                if (file_exists($file->fullpath))
                {
                    $is_preloaded_image = $document->storage->isImage() && $context->request->query->get('preload', 'boolean');

                    //Increase document hit count
                    if ($document->isHittable() && !$context->request->isStreaming() && !$is_preloaded_image) {
                        $document->hit();
                    }

                    //Set the data in the response
                    try
                    {
                        $this->getResponse()
                            ->attachTransport('stream')
                            ->setContent($file->fullpath, $document->mimetype ?: 'application/octet-stream');
                    }
                    catch (\InvalidArgumentException $e) {
                        throw new Library\ControllerExceptionResourceNotFound('File not found');
                    }
                }
                else  throw new Library\ControllerExceptionResourceNotFound('File not found');
            }
            else throw new \RuntimeException('Stream wrapper is missing');
        }
        else throw new Library\ControllerExceptionResourceNotFound('Document not found');
    }
}
