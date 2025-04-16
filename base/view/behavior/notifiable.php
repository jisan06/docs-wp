<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;
use EasyDocLabs\WP;

class ViewBehaviorNotifiable extends Library\ViewBehaviorAbstract
{
    protected $_notifiers = [];

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        foreach ($config->notifiers as $notifier) {
            $this->_notifiers[$notifier] = $this->getObject($notifier);
        }
    }

    protected function _beforeRender(Library\ViewContextTemplate $context)
    {
        $entity = $this->getModel()->fetch();

        if ($entity->isNotifiable())
        {
            $context->data->notifications     = $entity->getNotifications(['notifier' => array_keys($this->_notifiers)]);
            $context->data->notifiers         = $this->_notifiers;
            $context->data->notifiable_entity = $this->getModel()->fetch();

            $notifiable_data = [
                'notifications' => $context->data->notifications->toArray(),
                'url'           => $this->getRoute('view=notification&format=json'),
                'row'           => $context->data->notifiable_entity->id,
                'table'         => $context->data->notifiable_entity->getTable()->getName(),
                'notifiers'     => []
            ];

            foreach ($this->_notifiers as $identifier => $notifier) {
                $notifiable_data['notifiers'][$identifier] = $notifier->getData();
            }

            $notifiable_data['debug'] = WP::isFolioDebug();

            $context->data->notifiable_data = $notifiable_data;
        }
    }
}