<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

/**
 * Used by the document controller to make an image thumbnail out of the file if possible
 */
class ControllerBehaviorThumbnailable extends Library\ControllerBehaviorAbstract
{
    /**
     * If set to true in before.edit, after.edit will regenerate the thumbnail
     */
    protected $_regenerate;

    public function generateThumbnail(Library\ModelEntityInterface $entity)
    {
        $config = $this->getObject('com://admin/easydoc.model.configs')->fetch();

        if ($config->thumbnails)
        {
            $controller = $this->getObject('com:easydoc.controller.thumbnail');
            $context    = $controller->getContext();

            $context->setAttribute('entity', $entity);

            $controller->execute('generate', $context);
        }
    }

    public static function convertToBytes($value)
    {
        $keys = ['k', 'm', 'g'];
        $last_char = strtolower(substr($value, -1));
        $value = (int) $value;

        if (in_array($last_char, $keys)) {
            $value *= pow(1024, array_search($last_char, $keys)+1);
        }

        return $value;
    }

    /**
     * Create a thumbnail for new files
     *
     * @param Library\ControllerContextInterface $context
     */
    protected function _afterAdd(Library\ControllerContextInterface $context)
    {
        if ($context->response->getStatusCode() !== 201 || !empty($context->result->image) || !$context->result->automatic_thumbnail) {
            return;
        }

        $this->generateThumbnail($context->result->getIterator()->current());
    }

    /**
     * Figure out if the file has changed and if so regenerate the thumbnail on after save
     *
     * @param Library\ControllerContextInterface $context
     */
    protected function _beforeEdit(Library\ControllerContextInterface $context)
    {
        $item = $this->getModel()->fetch();
        $data = $context->request->data;

        $controller = $this->getObject('com:easydoc.controller.thumbnail');
        $filename   = $controller->getDefaultFilename($item);

        if ($data->regenerate_thumbnail) {
            $this->_regenerate = true;
        }

        if ($data->regenerate_thumbnail_if_automatic && ($item->image === $filename)) {
            $this->_regenerate = true;
        }

        // None or custom to automatic thumbnail.
        if ($data->automatic_thumbnail && ($data->image !== $filename)) {
            $this->_regenerate = true;
        }

        if ($data->image && ($data->image === $filename))
        {
            // Force a re-generate if the document file changes.
            if ($data->storage_path && ($item->storage_path !== $data->storage_path)) {
                $this->_regenerate = true;
            }

            // Make sure that the thumb still exists ... re-generate if it doesn't.
            if (!file_exists($controller->getContainer()->fullpath . '/' . $filename)) {
                $this->_regenerate = true;
            }
        }
    }

    protected function _afterEdit(Library\ControllerContextInterface $context)
    {
        $status_code = $context->getResponse()->getStatusCode();

        if ($status_code < 200 || $status_code >= 300) {
            return;
        }

        if ($this->_regenerate)
        {
            foreach ($context->result as $entity) {
               $this->generateThumbnail($entity);
            }
        }
    }

    /**
     * Remove the attached thumbnail
     *
     * @param Library\ControllerContextInterface $context
     */
    protected function _afterDelete(Library\ControllerContextInterface $context)
    {
        $controller = $this->getObject('com:easydoc.controller.thumbnail');

        foreach ($context->result as $entity)
        {
            $default = $controller->getDefaultFilename($entity);

            $thumbnails = [$default];

            // Check if a custom thumbnail is set.
            if (($thumbnail = $entity->image) && ($thumbnail != $default))
            {
                // See if the custom image is being used on another document and mark it for deletion.
                if ($this->getObject('com://admin/easydoc.model.documents')->image($thumbnail)->count() == 0) {
                    $thumbnails[] = $thumbnail;
                }
            }

            // Delete thumbnails.
            foreach ($thumbnails as $thumbnail)
            {
                if (file_exists($controller->getContainer()->fullpath . '/' . $thumbnail))
                {
                    try
                    {
                        $this->getObject('com:files.controller.file')
                            ->container('easydoc-images')
                            ->folder(dirname($thumbnail))
                            ->name(basename($thumbnail))
                            ->delete();

                    } catch (KControllerException $e) {
                        // Do nothing.
                    }
                }
            }

            // Reset image on all documents making use of the default thumbnail.
            $documents = $this->getObject('com://admin/easydoc.model.documents')->image($default)->fetch();

            if (count($documents))
            {
                $documents->image = "";
                $documents->save();
            }
        }
    }
}
