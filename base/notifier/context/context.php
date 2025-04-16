<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class NotifierContext extends Library\Command implements NotifierContextInterface
{
    public function setEntity(Library\ModelEntityInterface $entity)
    {
        return Library\ObjectConfig::set('entity', $entity);
    }

    public function getEntity()
    {
        return Library\ObjectConfig::get('entity');
    }

    public function setNotification(ModelEntityNotification $notification)
    {
        return Library\ObjectConfig::set('notification', $notification);
    }

    public function getNotification()
    {
        return Library\ObjectConfig::get('notification');
    }

    public function setRecipient($recipient)
    {
        return Library\ObjectConfig::set('recipient', $recipient);
    }

    public function getRecipient()
    {
        return Library\ObjectConfig::get('recipient');
    }

    public function setActor(Library\UserInterface $actor)
    {
        return Library\ObjectConfig::set('actor', $actor);
    }

    public function getActor()
    {
        return Library\ObjectConfig::get('actor');
    }

    public function setAction($action)
    {
        return Library\ObjectConfig::set('action', $action);
    }

    public function getAction()
    {
        return Library\ObjectConfig::get('action');
    }
}
