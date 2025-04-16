<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

class ControllerCategory extends Base\ControllerModel
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->addCommandCallback('before.delete', '_checkDocumentCount');
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['behaviors' => ['com:easydoc.controller.behavior.notifiable']]);

        parent::_initialize($config);
    }

    /**
     * Halts the delete if the category has documents attached to it.
     *
     * Also makes sure subcategories are deleted correctly when both
     * they and their parents are in the rowset to be deleted.
     *
     * @param Library\DispatcherContextInterface $context
     * @throws Library\ControllerExceptionActionFailed
     */
    protected function _checkDocumentCount(Library\ControllerContextInterface $context)
    {
        $model = $this->getModel();

        $model->getState()->documents_count = true;

        $data = $model->fetch();

        if ($count = $data->_documents_count)
        {
            $message = $this->getObject('translator')->choose([
                'This category or its children has a document attached. You first need to delete or move it before deleting this category.',
                'This category or its children has {count} documents attached. You first need to delete or move them before deleting this category.'
            ], $count, ['count' => $count]);

            if ($context->getRequest()->getFormat() === 'html') {
                $context->getResponse()->addMessage($message, Library\ControllerResponse::FLASH_ERROR);
                $context->response->setRedirect($this->getRequest()->getReferrer());

                return false;
            } else {
                throw new Library\ControllerExceptionActionFailed($message);
            }
        }

        /*
         * Removes the child categories from the rowset since they will be deleted by their parent.
         * Otherwise rowset gets confused when it tries to delete a non-existant row.
         */
        if ($data instanceof Library\ModelEntityInterface)
        {
            $to_be_deleted = [];

            // PHP gets confused if you extract a row and then continue iterating on the rowset
            $iterator = clone $data;
            foreach ($iterator as $entity)
            {
                if (in_array($entity->id, $to_be_deleted)) {
                    $data->remove($entity);
                }

                foreach ($entity->getDescendants() as $descendant) {
                    $to_be_deleted[] = $descendant->id;
                }
            }
        }
    }
}