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

/**
 * Used by the node controller to change document paths after moving files
 */
class ControllerBehaviorOptionable extends EasyDoc\ControllerBehaviorOptionable
{
    protected function _beforeRender(Library\ControllerContext $context)
    {
        $this->_setQueryFilters($context);

        parent::_beforeRender($context);
    }

    protected function _setQueryFilters(Library\ControllerContext $context)
    {
        $options = $this->getOptions();

        $controller = $context->getSubject();

        if ($filters = $controller->getConfig()->query_filters)
        {
            $query = $context->getRequest()->getQuery();

            foreach ($filters as $filter => $vars)
            {
                $options->{$filter} = [];

                foreach ($vars as $var => $config)
                {
                    if (is_numeric($var))
                    {
                        $var = $config;
                        
                        $config = new Library\ObjectConfig(['filter' => 'cmd', 'name' => $var]);
                    }
        
                    $config->append(['filter' => 'cmd']);
        
                    if (!empty($query->{$var}))
                    {
                        $value = $this->getObject('lib:filter.' . $config->filter)->sanitize($query->{$var});
                
                        if (count($vars) === 1) {
                            $options->{$filter}->append($value);
                        } else {
                            $options->{$filter}->append([$config->name => $value]);
                        }  
                    }            
                }
            }
        }

        $options->append(['site' => [
            'url' => $this->getObject('request')->getSiteUrl()
        ],
            'request'                => [
                'referrer' => $this->getObject('request')->getReferrer(),
                'url'      => $this->getObject('request')->getUrl()
            ]
        ]);
    }
}