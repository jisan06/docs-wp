<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\EasyDoc;
use EasyDocLabs\Library;

class ControllerCategory extends EasyDoc\ControllerCategory
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->addCommandCallback('after.read'   , '_populateCategory');
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['formats' => ['json'], 'behaviors' => ['hideable', 'organizable']]);

        parent::_initialize($config);
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

            if ($this->getRequest()->getFormat() === 'html' && $view->getName() == 'category')
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
                    $context->result->parent_id = $id;
                    $context->result->category_slug = $slug;
                }
            }
        }
    }
}