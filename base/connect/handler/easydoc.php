<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;
use Foliokit;

class ConnectHandlerEasydoc extends ConnectHandlerAbstract
{
    private static $_editable_image_containers = ['easydoc-files', 'easydoc-images', 'easydoc-icons'];
    private static $_editable_image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['tasks' => ['download', 'serve', 'image-editor']]);

        parent::_initialize($config);
    }

    /**
     * Handles Image Editor related requests
     *
     * @param Library\ControllerContextInterface $context
     */
    public function _taskImageEditor(Library\ControllerContextInterface $context)
    {
        $request = $context->getRequest();

        if ($request->isGet()) {
            $this->_serveFile($context); // Serve file to editor
        } else {
            $this->_saveFile($context); // POST request, save modified image
        }
    }

    /**
     * Endpoint for downloading files from site
     *
     * @param Library\ControllerContextInterface $context
     */
    public function _taskDownload(Library\ControllerContextInterface $context)
    {
        $request = $context->getRequest();
        $query   = $request->getQuery();

        if ($query->has('image')) {
            $this->_serveFile($context);
        } elseif ($query->has('id')) {
            $this->_serveDocument($context);
        }
    }

    /**
     * Endpoint for serving files to the site
     *
     * @param Library\ControllerContextInterface $context
     */
    public function _taskServe(Library\ControllerContextInterface $context)
    {
        $request = $context->getRequest();
        $query   = $request->getQuery();

        if (!$query->has('image'))
        {
            $result = array(
                'result' => $this->_updateThumbnail($context)
            );

            $context->getResponse()->setContent(\EasyDocLabs\WP::wp_json_encode($result), 'application/json');
        }
        else $this->_saveFile($context);

    }

    protected function _parseFile($path)
    {
        $parts = explode('://', $path, 2);

        if (count($parts) !== 2) {
            throw new \UnexpectedValueException('Invalid path: '.$path);
        }

        list($container_slug, $filepath) = $parts;

        if (!$this->getObject('com:files.model.containers')->slug($container_slug)->count()) {
            throw new \UnexpectedValueException('Container not found: '.$container_slug);
        }

        if (!in_array($container_slug, static::$_editable_image_containers)) {
            throw new \UnexpectedValueException('Invalid container: '.$container_slug);
        }

        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

        if (!in_array($extension, static::$_editable_image_extensions)) {
            throw new \UnexpectedValueException('Invalid file extension: '.$extension);
        }

        if (strpos($filepath, '/') === false) {
            $folder   = '';
            $filename = $filepath;
        } else {
            $folder    = substr($filepath, 0, strrpos($filepath, '/'));
            $filename  = Foliokit\basename($path);
        }

        return [
            'container' => $container_slug,
            'folder' => urldecode($folder),
            'name'   => urldecode($filename)
        ];
    }

    protected function _saveFile(Library\ControllerContextInterface $context)
    {
        $request = $context->getRequest();
        $data    = $request->getData();

        if (!$request->files->has('file')) {
            throw new \UnexpectedValueException('File is missing');
        }

        if (!$data->has('path')) {
            throw new \UnexpectedValueException('Destination path is missing');
        }

        $path = $data->get('path', 'url');
        $data = array_merge($this->_parseFile($path), [
            'file'      => $request->files->file['tmp_name'],
            'overwrite' => true
        ]);

        $entity = $this->getObject('com:files.controller.file', ['behaviors' => [
            'permissible' => [
                'permission' => 'com:easydoc.controller.permission.yesman'
            ]
        ]])->container($data['container'])->add($data);

        $result = [
            'entity' => $entity->toArray()
        ];

        if ($data['container'] === 'easydoc-files') {
            try {
                $storage_path = (!empty($data['folder']) ? $data['folder'].'/' : '') . $data['name'];

                $controller = $this->getObject('com://admin/easydoc.controller.document', ['behaviors' => [
                    'permissible' => [
                        'permission' => 'com://admin/easydoc.controller.permission.yesman'
                    ]
                ]]);

                $controller->storage_path($storage_path);
                $controller->edit(['regenerate_thumbnail_if_automatic' => true]);
            }
            catch (\Exception $e) {
                if (\Foliokit::isDebug()) throw $e;
            }
        }

        $this->getObject('response')->setContent(\EasyDocLabs\WP::wp_json_encode($result), 'application/json');
    }

    protected function _serveFile(Library\ControllerContextInterface $context)
    {
        $request = $context->getRequest();
        $query   = $request->getQuery();

        if (!$query->has('path')) {
            throw new \UnexpectedValueException('Destination path is missing');
        }

        $path = $query->get('path', 'url');

        $controller = $this->getObject('com:files.controller.file', ['behaviors' => [
            'permissible' => [
                'permission' => 'com:easydoc.controller.permission.yesman'
            ]
        ]]);

        $controller->getRequest()->getQuery()->add($this->_parseFile($path));

        $file = $controller->read($this->_parseFile($path));

        if ($file->isNew() || !is_file($file->fullpath)) {
            throw new Library\ControllerExceptionResourceNotFound('File not found');
        }

        $context->getResponse()->attachTransport('stream')
                ->setContent($file->fullpath, $file->mimetype ?: 'application/octet-stream')
                ->getHeaders()->set('Access-Control-Allow-Origin', '*');
    }

    /**
     * Serve a document for the consumption of the thumbnail service
     *
     * @param Library\ControllerContextInterface $context
     */
    protected function _serveDocument(Library\ControllerContextInterface $context)
    {
        $id = $context->getRequest()->getQuery()->id;

        $document = $this->getObject('com:easydoc.model.documents')->id($id)->fetch();

        if ($document->isNew()) {
            throw new Library\ControllerExceptionResourceNotFound('Document not found');
        }

        $file = $document->storage;

        if ($file->isNew() || !is_file($file->fullpath)) {
            throw new Library\ControllerExceptionResourceNotFound('File not found');
        }

        $context->getResponse()->attachTransport('stream')
                ->setContent($file->fullpath, $document->mimetype ?: 'application/octet-stream')
                ->getHeaders()->set('Content-Disposition', ['attachment' => ['filename' => '"file"']]);
    }

    /**
     * Updates the document thumbnail from the request payload
     *
     * @param Library\ControllerContextInterface $context
     * @return boolean
     */
    protected function _updateThumbnail(Library\ControllerContextInterface $context)
    {
        $request = $context->getRequest();
        $data    = $request->getData();

        $user_data = $data->user_data;

        if (!isset($user_data['uuid'])) {
            throw new \RuntimeException('Missing user data');
        }

        $scan     = $this->getObject('com:easydoc.model.scans')->identifier($user_data['uuid'])->fetch();
        $document = $this->getObject('com:easydoc.model.documents')->uuid($user_data['uuid'])->fetch();

        if ($document->isNew()) {
            throw new \RuntimeException('Document not found');
        }

        if ($document->isLocked())
        {
            $document->locked_by = $document->locked_on = null;
            $document->save();
        }

        if ($scan->thumbnail && isset($data->thumbnail_url))
        {
            $controller = $this->getObject('com:easydoc.controller.thumbnail');
            $context    = $controller->getContext();

            $context->setAttribute('entity', $document)
                    ->setAttribute('thumbnail', $data->thumbnail_url);

            $controller->execute('save', $context);
        }

        if ($scan->ocr && isset($data->contents_url))
        {
            try
            {
                $file = $this->getObject('com:files.model.entity.url');
                $file->setProperties(array('file' => $data->contents_url));

                if ($file->contents)
                {
                    $document->contents = $file->contents;
                    $document->save();
                }
            }
            catch (\Exception $e) {}
        }

        if (!empty($data->error))
        {
            $scan->status = ControllerBehaviorScannable::STATUS_FAILED;
            $scan->save();
        }
        else $scan->delete();

        return true;
    }
}
















