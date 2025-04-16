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

class ControllerDocument extends Base\ControllerModel
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->addCommandCallback('after.read'   , '_populateCategory');

        // @todo Implement replacement to menu settings
        // $this->addCommandCallback('before.render', '_checkDownloadLink');
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors' => [
                'thumbnailable',
                'organizable',
                'com:tags.controller.behavior.taggable',
                'com:easydoc.controller.behavior.notifiable',
                'com://site/easydoc.controller.behavior.filterable' => [
                    'options' => ['sort' => 'sort_documents']
                ],
                'restrictable' => ['actions' => ['edit', 'delete', 'upload']]
            ],
            'formats'   => ['json']
        ]);


        parent::_initialize($config);
    }

    /**
     * Check to see if we need to redirect to the download view or a remote URL
     *
     * @param Library\ControllerContextInterface $context
     * @return bool|void
     */
    protected function _checkDownloadLink(Library\ControllerContextInterface $context)
    {
        $entity = $this->getModel()->fetch();
        $query  = $this->getRequest()->query;

        // TODO: Redirect document view to download view if the title links are set as download in menu parameters
        if (!$entity->isNew() && $this->getRequest()->getFormat() === 'html'
            && $query->view === 'document' && $this->getView()->getLayout() === 'default')
        {
            $url  = $this->getView()->getRoute($entity, "layout=file");
            $context->response->setRedirect($url);
        }

        return true;
    }

    /**
     * If the category slug is supplied in the URL, prepopulate it in the new document form
     *
     * @param Library\ControllerContextInterface $context
     */
    protected function _populateCategory(Library\ControllerContextInterface $context)
    {
        if ($context->result->isNew())
        {
            $query = $this->getRequest()->query;
            $view = $this->getView();

            if ($this->getRequest()->getFormat() === 'html' && $view->getName() == 'document')
            {
                $slug = $query->category_slug;

                if (empty($slug) && $query->path)
                {
                    $slug = explode('/', $query->path);
                    $slug = array_pop($slug);
                }

                if (!empty($slug))
                {
                    $id = $this->getObject('com://site/easydoc.model.categories')->slug($slug)->fetch()->id;
                    $context->result->easydoc_category_id = $id;
                    $context->result->category_slug = $slug;
                }
            }
        }
    }
}