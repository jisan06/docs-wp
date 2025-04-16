<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

class ControllerSubmit extends Base\ControllerModel
{
    /**
     * A reference to the uploaded file row
     * Used to delete the file if the add action fails
     */
    protected $_uploaded;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->addCommandCallback('before.add', '_uploadFile');
        $this->addCommandCallback('after.add' , '_cleanUp');
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors' => [
                'thumbnailable',
                'notifiable',
                'editable',
                'restrictable' => [
                    'actions' => ['add']
                ],
                'com:easydoc.controller.behavior.notifiable',
                'optionable' => [
                    'defaults' => [
                        'category_children' => true
                    ]
                ]
            ],
            'toolbars'  => ['submit'],
            'model'     => 'documents',
            'formats'   => ['json']
        ]);

        parent::_initialize($config);
    }

    protected function _actionSave(Library\ControllerContextInterface $context)
    {
        $result = $this->execute('add', $context);

        $chunk  = $context->request->data->get('chunk', 'int');
        $chunks = $context->request->data->get('chunks', 'int');

        $upload_finished = (!$chunks || $chunk == $chunks - 1);

        if ($upload_finished && $context->getResponse()->getStatusCode() === Library\HttpResponse::CREATED)
        {
            if ($context->request->data->redirect) {
                $route = $this->getObject('http.url', [
                    'url' => $context->request->data->redirect
                ]);
            } else {
                $route = $this->getRequest()->getUrl();
            }

            $route->setQuery(['submit_success' => 1], true);
            $route->setHost($this->getRequest()->getUrl()->getHost());

            //$route = $this->getView()->getRoute(['component' =>'easydoc', 'view' =>'submit', 'layout' =>'success', 'format' =>'html']);

            if ($context->getRequest()->getFormat() === 'html') {
                $context->response->setRedirect($route);
            } else {
                $context->response->setContent(\EasyDocLabs\WP::wp_json_encode([
                    'redirect' => (string) $route
                ]), 'application/json');
            }
        }

        return $result;
    }

    public function getView()
    {
        if (!$this->_view instanceof Library\ViewInterface)
        {
            $view = parent::getView();

            $options = $this->getOptions();

            if ($categories = Library\ObjectConfig::unbox($options->get('category_id')))
            {
                $model = $this->getObject('com:easydoc.model.categories')
                              ->permission('upload_document')
                              ->user($this->getUser()->getId());

                if ($options->get('category_children')) {
                    $model->parent_id($categories)->include_self(true);
                }

                $result = $model->allowed();
            }
            else $result = $this->canAdd();

            $view->show_form = $result;
        }

        return $this->_view;
    }

    protected function _setData(Library\ControllerContextInterface $context)
    {
        $data    = $context->request->data;
        $options = $this->getOptions();

        $translator = $this->getObject('translator');

        foreach ($this->getModel()->getTable()->getColumns() as $key => $column) {
            if (!in_array($key, ['easydoc_category_id', 'storage_type', 'title', 'description'])) {
                unset($data->$key);
            }
        }

        $data->enabled = $options->get('auto_publish') ? 1 : 0;

        if (empty($data->storage_type)) {
            $data->storage_type = $data->storage_path_remote ? 'remote' : 'file';
        }

        if ($data->storage_type === 'file')
        {
            $file = $context->request->files->file;

            if (empty($file) || empty($file['name'])) {
                throw new Library\ControllerExceptionRequestInvalid($translator->translate('You did not select a file to be uploaded.'));
            }
        }
        else $data->storage_path = $data->storage_path_remote;
    }

    protected function _getFileController(Library\ControllerContextInterface $context)
    {
        return $this->getObject('com:files.controller.file', [
            'behaviors' => [
                'com:easydoc.controller.behavior.movable',
                'com:easydoc.controller.behavior.syncable',
                'permissible' => [
                    'permission' => 'com:easydoc.controller.permission.file'
                ]
            ],
            'request' => clone $context->request
        ])->container('easydoc-files');
    }

    protected function _uploadFile(Library\ControllerContextInterface $context)
    {
        $result = true;

        try
        {
            $this->_setData($context);

            $data = $context->request->data;

            if ($data->storage_type === 'file')
            {
                $file = $context->request->files->file;

                $controller = $this->_getFileController($context);
                $category   = $this->getObject('com://admin/easydoc.model.categories')->id($data->easydoc_category_id)->fetch();

                if ($category->canUpload())
                {
                    $folder = $category->folder;

                    $filename = $data->has('name') ? $data->name : $file['name'];
                    $filename = $this->_getUniqueName($controller->getModel()->getContainer(), $folder, $filename);

                    $this->_uploaded = $controller
                        ->add([
                            'file'   => $file['tmp_name'],
                            'name'   => $filename,
                            'folder' => $folder,
                            'chunk'  => $context->request->data->get('chunk', 'int'),
                            'chunks' => $context->request->data->get('chunks', 'int')
                        ]);

                    if ($this->_uploaded)
                    {
                        $data->storage_path = $this->_uploaded->path;

                        $context->setEntity($this->getModel()->create($data->toArray()));
                    }
                    else $result = false; // This happens when we upload just a chunk
                }
                else throw new Library\ControllerExceptionRequestNotAuthorized('Uploads are not allowed in this category');
            }
        }
        catch (\Exception $exception)
        {
            if ($context->getRequest()->getFormat() !== 'json')
            {
                $message = $exception->getMessage();
                $context->getResponse()->setRedirect($this->getRequest()->getReferrer(), $message, 'error');
                $context->getResponse()->send();

                $result = false;
            }
            else
            {
                $context->response->setContent(\EasyDocLabs\WP::wp_json_encode([
                    'status' => false,
                    'error' => $exception->getMessage()
                ]), 'application/json');

                $result = false;
            }
        }

        return $result;
    }

    /**
     * Find a unique name for the given container and folder by adding (1) (2) etc to the end of file name
     *
     * @param $container
     * @param $folder
     * @param $file
     * @return string
     */
    protected function _getUniqueName($container, $folder, $file)
    {
        $adapter   = $this->getObject('com:files.adapter.file');
        $folder    = $container->fullpath.(!empty($folder) ? '/'.$folder : '');
        $fileinfo  = \Foliokit\pathinfo($file);
        $filename  = ltrim($fileinfo['filename']);
        $extension = $fileinfo['extension'];

        $adapter->setPath($folder.'/'.$file);

        $i = 1;
        while ($adapter->exists())
        {
            $file = sprintf('%s (%d).%s', $filename, $i, $extension);

            $adapter->setPath($folder.'/'.$file);
            $i++;
        }

        return $file;
    }

    protected function _cleanUp(Library\ControllerContextInterface $context)
    {
        if ($context->getResponse()->getStatusCode() !== Library\HttpResponse::CREATED)
        {
            try
            {
                if ($this->_uploaded instanceof Library\ModelEntityInterface) {
                    $this->_uploaded->delete();
                }

            } catch (Exception $e) {
                // Well, we tried
            }
        }
    }
}
