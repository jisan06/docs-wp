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
use EasyDocLabs\WP;

class Dispatcher extends EasyDoc\Dispatcher
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'authenticators' => ['jwt' => ['secret' => WP::wp_salt('AUTH_SALT')]],
        ]);
        
        parent::_initialize($config);
    }

    public function getRequest()
    {
        $request = parent::getRequest();

        $query = $request->getQuery();

        $user = $this->getObject('user');

        if (!$user->authorise('publish_posts'))
        {
            $query->enabled = 1;
            $query->status  = 'published';
        }

        $query->page = $query->Itemid;

        if ($query->has('view') && in_array($query->get('view', 'cmd'), ['list', 'tree'])) {
            $query->documents_count = true;
        }

        // This cannot come from the query string on frontend
        unset($query->group);

        return $request;
    }

    protected function _beforeSend(Library\DispatcherContext $context)
    {
        $controller = $this->getController();
        
        if (in_array($controller->getIdentifier()->getName(), ['document', 'category']) && 
            $controller->getRequest()->getFormat() == 'html')
        {
            if ($controller->getView()->getLayout() == 'form') {
                $context->getResponse()->getHeaders()->set('Cache-Control', 'no-store');
            }
        }
    }
}
