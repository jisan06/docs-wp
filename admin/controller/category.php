<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Admin;

use EasyDocLabs\EasyDoc;
use EasyDocLabs\Library;

class ControllerCategory extends EasyDoc\ControllerCategory
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->addCommandCallback('after.delete', '_setRedirectToList');
    }

    protected function _beforeBrowse(Library\ControllerContextInterface $context)
    {
        $this->getModel()->documents_count(true)->documents_count_access(false);
    }

    protected function _fetchEntity(Library\ControllerContext $context)
    {
        if(!$context->result instanceof ModelEntityInterface && $context->action == 'delete') {
            $this->getModel()->documents_count(true);
        }

        parent::_fetchEntity($context);
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors' => [
                'organizable',
                'restrictable' => [
                    'redirect_url' => 'admin.php?page=easydoc-settings'
                ],
                'sortable',
            ]
        ]);

        parent::_initialize($config);
    }

    /**
     * Redirect to the list
     *
     * @param Library\ControllerContextInterface $context
     * @return void
     */
    protected function _setRedirectToList(Library\ControllerContextInterface $context)
    {
        $route = $this->getObject('router')->generate('easydoc:', ['view' => 'categories']);

        $context->response->setRedirect($route);
    }
}
