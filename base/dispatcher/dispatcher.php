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

class Dispatcher extends Base\Dispatcher
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'controller' => 'document',
            'behaviors'  => [
                'fillable',
                'licensable',
                'fileable',
            ]
        ]);

        parent::_initialize($config);
    }

    protected function _beforeDispatch(Library\DispatcherContext $context)
    {
        $this->getObject('translator')->load('com:files');
    }

    public function getRequest()
    {
        $request = parent::getRequest();

        $query = $request->getQuery();

        if ($query->view == 'usergroups') {
            $query->internal = 0;
        }

        $user = $this->getObject('user');

        if (!is_super_admin($user->getId())) {
            $query->access = $user->getId();
        }

        return $request;
    }
}