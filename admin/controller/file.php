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

class ControllerFile extends Base\ControllerView
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'view' => 'files'
        ]);

        parent::_initialize($config);
    }

    public function getRequest()
    {
        $request = parent::getRequest();

        // This is used to circumvent the URL size exceeding 2k bytes problem for "create documents" screen
        if ($request->data->has('paths')) {
            $request->query->paths = $request->data->paths;
        }

        return $request;
    }

    public function getView()
    {
        $view    = parent::getView();
        $request = $this->getRequest();

        if ($request->query->callback && $request->query->layout === 'select') {
            $view->callback = $request->query->callback;
        }

        if ($request->query->paths && $request->query->layout === 'form') {
            $view->paths = $request->query->paths;
        }

        return $view;
    }
}
