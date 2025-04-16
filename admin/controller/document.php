<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\Component\Base;
use EasyDocLabs\Library;

class ControllerDocument extends Base\ControllerModel
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->addCommandCallback('after.read', '_setDefaults');
        $this->addCommandCallback('after.delete', '_setRedirectToList');
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors' => [
                'sortable',
                'organizable',
                'thumbnailable',
                'restrictable' => [
                    'redirect_url' => 'admin.php?page=easydoc-settings'
                ],
                'com:tags.controller.behavior.taggable',
                'com:easydoc.controller.behavior.notifiable',
                'scannable'
            ],
        ]);

        parent::_initialize($config);
    }

    public function getRequest()
    {
        $request = parent::getRequest();

        // This is used to circumvent the URL size exceeding 2k bytes problem for document counts in files view
        if ($request->query->view === 'documents' && $request->data->has('storage_path')) {
            $request->query->storage_path = $request->data->storage_path;
        }

        return $request;
    }

    /**
     * Preset some fields in the edit form from request variables
     *
     * @param Library\ControllerContextInterface $context
     */
    protected function _setDefaults(Library\ControllerContextInterface $context)
    {
        $request = $this->getRequest();
        $view = $this->getView();

        if ($context->result->isNew())
        {
            if ($request->getFormat() == 'html' && $view->getName() == 'document')
            {
                if (!empty($request->query->storage_path)) {
                    $context->result->storage_path = $request->query->storage_path;
                    $context->result->storage_type = 'file';
                }
            }

            if ($request->query->storage_type) {
                $context->result->storage_type = $request->query->storage_type;
            }

            if ($request->query->category) {
                $context->result->easydoc_category_id = $request->query->category;
            }
        }
    }

    /**
     * Redirect to the list
     * 
     * @todo Temporary fix for proper redirection after deletion from edit form (read) https://github.com/easydoclabs/easydoc/issues/1
     * @param Library\ControllerContextInterface $context
     * @return void
     */
    protected function _setRedirectToList(Library\ControllerContextInterface $context)
    {
        $request    = $this->getRequest();
        $base_path  = $request->getBasePath();
        $identifier = $this->getIdentifier();
        $view       = Library\StringInflector::pluralize($identifier->name);
        $url        = sprintf($base_path . '/admin.php?component=%s&view=%s&page=easydoc-documents', $identifier->package, $view);

        $context->response->setRedirect($this->getObject('lib:http.url', ['url' => $url]));
    }

    /**
     * Re-set the redirect when needed in the overridden getReferrer method
     *
     * @param Library\ControllerContextInterface $context
     */
    protected function _afterSave(Library\ControllerContextInterface $context)
    {
        if($context->result && $context->result->getStatus() !== Library\ModelEntityInterface::STATUS_FAILED) {
            $context->response->setRedirect($this->getReferrer($context));
        }
    }

    /**
     * Redirects batch edits to documents view.
     *
     * @param Library\ControllerContextInterface $context
     * @return KObjectInterface
     */
    public function getReferrer(Library\ControllerContextInterface $context)
    {
        $referrer = parent::getReferrer($context);

        if ($referrer instanceof Library\HttpUrl)
        {
            $query = $referrer->query;

            $is_easydoc = isset($query['option']) && $query['option'] == 'com_easydoc';
            $is_files  = isset($query['view']) && $query['view'] == 'files';
            $is_form   = isset($query['layout']) && $query['layout'] == 'form';

            if ($is_easydoc && $is_files && $is_form)
            {
                $referrer = $this->getObject('lib:http.url', [
                    'url' => $this->getView()->getRoute(['view' => 'documents'])
                ]);
            }
        }

        return $referrer;
    }


    protected function _actionCopy(Library\ControllerContextInterface $context)
    {
        if(!$context->result instanceof Library\ModelEntityInterface) {
            $entities = $this->getModel()->fetch();
        } else {
            $entities = $context->result;
        }

        if(count($entities))
        {
            foreach($entities as $entity)
            {
                unset($entity->id);
                unset($entity->uuid);
                $entity->setStatus(Library\Database::STATUS_DELETED);
                $entity->setProperties($context->request->data->toArray());
            }

            //Only throw an error if the action explicitly failed.
            if($entities->save() === false)
            {
                $error = $entities->getStatusMessage();
                throw new Library\ControllerExceptionActionFailed($error ? $error : 'Copy Action Failed');
            }
            else $context->status = $entities->getStatus() === Library\Database::STATUS_CREATED ? Library\HttpResponse::CREATED : Library\HttpResponse::NO_CONTENT;
        }
        else throw new Library\ControllerExceptionResourceNotFound('Resource could not be found');

        return $entities;
    }

}
