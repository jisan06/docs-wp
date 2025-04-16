<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

interface NotifierContextInterface extends Library\CommandInterface
{
    public function setEntity(Library\ModelEntityInterface $entity);

    public function getEntity();

    public function setNotification(ModelEntityNotification $notification);

    public function getNotification();

    public function setRecipient($recipient);

    public function getRecipient();

    public function setActor(Library\UserInterface $user);

    public function getActor();

    public function setAction($action);

    public function getAction();
}