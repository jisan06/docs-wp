<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */ 

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class DispatcherBehaviorFillable extends Library\DispatcherBehaviorAbstract
{
    protected function _beforeDispatch(Library\DispatcherContext $context)
    {
        $session = $this->getUser()->getSession();

        $data = Library\ObjectConfig::unbox($session->getContainer('attribute')->get('_fillable_data', []));

        if ($data)
        {
            if ($context->getRequest()->getQuery()->fillable)
            {
                $this->getController()->addCommandCallback('before.render', function(Library\ControllerContextInterface $context) use ($data)
                {        
                    if (!empty($data) && $context->getSubject() instanceof Library\ControllerViewable)
                    {
                        $view = $context->getSubject()->getView();
    
                        $ignore = $view->getConfig()->_ignore_fillable ?? false; // Allow views to disable fillable behavior
                        
                        if (!$ignore)
                        {
                            $view->addCommandCallback('before.render', function(Library\ViewContextInterface $context) use ($data)
                            {
                                if (!$context->getSubject()->isCollection())
                                {
                                    $entity = $context->getEntity();
        
                                    foreach($data as $key => $value)
                                    {
                                        $context->parameters->{$key} = $value;
                                        $entity->{$key}              = $value;
                                    }
                                }
                            });
                        }
                    }
                });
            }

            $session->getContainer('attribute')->remove('_fillable_data'); // Cleanup
        }
    }
}