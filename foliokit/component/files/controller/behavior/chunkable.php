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
 * Chunkable Controller Behavior
 *
 * Saves uploaded files in chunks before passing it to the entity
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ControllerBehaviorChunkable extends Library\ControllerBehaviorAbstract
{
    /**
     * A reference to the uploaded file in tmp directory
     *
     * @var string
     */
    protected $_temporary_file;

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'priority'   => self::PRIORITY_HIGH
        ]);

        parent::_initialize($config);
    }

    /**
     * Gathers file chunks into a file in the tmp directory
     *
     * @param Library\ControllerContextInterface $context
     * @return bool
     * @throws Library\ControllerExceptionActionFailed
     */
    protected function _beforeAdd(Library\ControllerContextInterface $context)
    {
        $request = $context->request;

        if (!$request->data->has('chunk') || !$request->data->has('name')) {
            return true;
        }

        if (!$request->files->has('file') || $request->files->file['error']) {
            throw new Library\ControllerExceptionRequestInvalid('Chunk has no file');
        }

        $chunk  = $request->data->get('chunk', 'int');
        $chunks = $request->data->get('chunks', 'int');
        $name   = $request->data->get('name', 'string');

        // Run filename validation for chunk 0
        if ($chunk === 0)
        {
            $context->request->data->file = $context->request->files->file['tmp_name'];

            $entity = $this->getModel()->create($context->request->data->toArray());

            $filter = $this->getObject('com:files.filter.file.name');
            $result = $filter->validate($entity);

            if ($result === false)
            {
                $errors = $filter->getErrors();
                if (count($errors)) {
                    throw new Library\ControllerExceptionActionFailed(array_shift($errors));
                }
            }
        }

        $folder = $this->getModel()->getContainer()->fullpath.'/.tmp';
        if (!is_dir($folder))
        {
            $result = mkdir($folder, 0755);

            if (!$result || !is_dir($folder)) {
                throw new Library\ControllerExceptionActionFailed('Unable to create tmp directory');
            }
        }

        $this->_temporary_file = $folder.'/'.$name;

        $output = @fopen($this->_temporary_file.'.part', $chunk == 0 ? 'wb' : 'ab');
        $input  = @fopen($request->files->file['tmp_name'], "rb");

        if (!$input || !$output) {
            throw new Library\ControllerExceptionActionFailed('Unable to open i/o files');
        }

        while ($buffer = fread($input, 4096)) {
            fwrite($output, $buffer);
        }

        @fclose($input);
        @fclose($output);

        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1)
        {
            // Strip the temp .part suffix off
            rename($this->_temporary_file.'.part', $this->_temporary_file);

            $context->request->data->file = new \SplFileObject($this->_temporary_file);
            if ($context->entity) {
                $context->entity->file = new \SplFileObject($this->_temporary_file);
            }
        }
        else
        {
            $data = [
                'status' => true
            ];

            $context->response->setContent(json_encode($data), 'application/json');

            return false;
        }
    }

    protected function _beforeEdit(Library\ControllerContextInterface $context)
    {
        $this->_beforeAdd($context);
    }

    /**
     * Removes the temporary file after upload
     *
     * @param Library\ControllerContextInterface $context
     */
    protected function _afterAdd(Library\ControllerContextInterface $context)
    {
        if ($this->_temporary_file && is_file($this->_temporary_file)) {
            @unlink($this->_temporary_file);
        }
    }

    protected function _afterEdit(Library\ControllerContextInterface $context)
    {
        $this->_afterAdd($context);
    }

}