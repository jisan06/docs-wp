<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class ModelEntityNotification extends Library\ModelEntityRow
{
    protected $_subject = null;

    public function send(NotifierContextInterface $context)
    {
        $result = false;

        if (!$this->isNew())
        {
            $context->setNotification($this);

            if ($notifier = $this->getNotifier()) {
                $result = $notifier->notify($context);
            }
        }

        return $result;
    }

    public function getNotifier()
    {
        $notifier = null;

        if (!$this->isNew()) {
            $notifier = $this->getObject($this->notifier);
        }

        return $notifier;
    }

    public function hasAction($action)
    {
        $result = false;

        if (!$this->isNew()) {
            $result = in_array($action, $this->getParameters()->actions->toArray());
        }

        return $result;
    }

    public function toArray()
    {
        $data = parent::toArray();

        $data['parameters'] = $this->getParameters()->toArray();

        return $data;
    }
}