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

class NotifierEmail extends NotifierAbstract
{
    const STATUS_PENDING = 0;

    const STATUS_SENT = 1;

    const STATUS_FAILED = 2;
    
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'job'     => 'com:easydoc.job.emails',
            'actions' => [
                'document' => ['add', 'edit', 'delete', 'download'],
                'category' => ['add', 'edit', 'delete']
            ]
        ]);

        parent::_initialize($config);
    }

    public static function send($recipient, $subject, $body, $headers = null)
    {   
        $from_name = WP::get_option('blogname');
        $mail_from = WP::get_option('admin_email');

        if (empty($headers))
        {
            $headers = [
                'Content-Type: text/html; charset=UTF-8',
                "From: {$from_name} <{$mail_from}>"
            ];
        }

        return WP::wp_mail($recipient, $subject, $body, $headers);
    }

    public function notify(NotifierContextInterface $context)
    {
        $recipients = $this->_getRecipients($context);

        if (!empty($recipients))
        {
            $translator = $this->getObject('translator');

            $controller = $this->getObject('com:easydoc.controller.email');

            foreach ($recipients as $email => $user)
            {
                $context->setRecipient($user);

                $parameters = $context->getNotification()->getParameters();

                if (!$parameters->subject) {
                    $parameters->subject = 'You have a notification from {sitename}';
                }

                // TODO: Replace controller call with lightweight tasks API for creating tasks

                $controller->add([
                    'recipient'    => $email,
                    'subject'      => $this->_processContent($context, 'subject'),
                    'notification' => $context->getNotification()->id,
                    'body'         => $this->_processContent($context)
                ]);
            }
        }
    }

    protected function _processContent(NotifierContextInterface $context, $type = 'body')
    {
        $parameters = $context->getNotification()->getParameters();

        $content = $parameters->{$type};

        preg_match_all('#\{(.+?)\}#', $content, $matches);

        $resolve = function($key, $object = null) use ($context, $content, &$resolve)
        {
            $value = null;

            if ($pos = strpos($key, '.')) {
                $property = substr($key, 0, $pos);
            } else {
                $property = $key;
            }

            $translator = $this->getObject('translator');

            if (!$object )
            {
                switch ($property)
                {
                    case 'entity':
                        $value = $context->getEntity();
                        break;
                    case 'actor':
                        $value = $context->getActor();
                        break;
                    case 'recipient':
                        $value = $context->getRecipient();
                        break;
                    case 'result':
                        $value = $translator->translate(sprintf('notifier %s result', str_replace('_', ' ', $context->getAction())));
                        break;
                    case 'action':
                        $value = $translator->translate(sprintf('notifier %s action', explode('_', $context->getAction())[0]));
                        break;
                    case 'sitename':
                        $value =  WP::get_option('blogname');
                        break;
                    case 'site_url':
                        $value = WP::site_url();
                        break;
                }
            }
            else
            {
                if ($object instanceof Library\UserInterface)
                {
                    if (in_array($property, ['name', 'email', 'username']))
                    {
                        $method = sprintf('get%s', ucfirst($property));
                        $value  = $object->$method();
                    }
                }

                if (!isset($value))
                {
                    switch ($property)
                    {
                        case '_type':
                            $value = $translator->translate($object->getIdentifier()->getName());
                            break;
                        case '_route':
                            $method = sprintf('_get%sRoute', ucfirst($object->getIdentifier()->getName()));
                            $value = (string) (method_exists($this, $method) ?  $this->$method($object) : $this->_getRoute($object));
                            break;
                    }
                }

                if (!isset($value) && isset($object->{$property})) $value = $object->{$property};
            }

            if ($property != $key && !is_scalar($value)) {
                $value = $resolve(substr($key, $pos + 1), $value);
            }

            if (!is_scalar($value)) {
                throw new \RuntimeException('Could not resolve the key value');
            }

            return $value;
        };

        foreach ($matches[1] as $match)
        {
            $key = strtolower($match);

            try {
                $content = str_replace(sprintf('{%s}', $match), $resolve($key), $content);
            } catch (\Exception $e) {
                // TODO: Throw exception if our debugger is enabled
            }
        }

        return $content;
    }

    protected function _getRecipients(NotifierContextInterface $context)
    {
        $recipients = [];

        $parameters = $context->getNotification()->getParameters();

        if (($groups = $parameters->groups) && $groups->count())
        {
            $entity = $context->getEntity();

            $model = $this->getObject('com:base.model.users')->addBehavior('com:easydoc.model.behavior.groupable');

            foreach ($groups as $group)
            {
                $model->reset()->getState()->reset();

                // Check for fixed groups

                if (in_array($group, array_values(self::FIXED_GROUPS)))
                {
                    $type  = $entity->getIdentifier()->getName();
                    $users = [];

                    switch ($group)
                    {
                        case self::FIXED_GROUPS['category_owner']:

                            // Category owner

                            $owner = null;

                            if ($type == 'document') {
                                $owner = $entity->category->created_by;
                            } elseif ($type == 'category') {
                                $owner = $entity->created_by;
                            }

                            if ($owner) {
                                $users[] = $owner;
                            }

                            break;

                        case self::FIXED_GROUPS['document_owner']:

                            // Document owner

                            if ($type == 'document')
                            {
                                if ($owner = $entity->created_by) {
                                    $users[] = $owner;
                                }
                            }

                            break;

                        default:

                            break;
                    }

                    if ($users) {
                        $users = $model->id($users)->fetch();
                    }

                }
                else $users = $model->group($group)->fetch();

                foreach ($users as $user)
                {
                    if (!isset($recipients[$user->getEmail()])) {
                        $recipients[$user->getEmail()] = $user;
                    }
                }
            }
        }

        if (($users = $parameters->users) && $users->count())
        {
            foreach ($this->getObject('com:base.model.users')->id($users->toArray())->fetch() as $user) {
                if (!isset($recipients[$user->getEmail()])) $recipients[$user->getEmail()] = $user;
            }
        }

        $current = $this->getObject('user');

        // Do not notify own actions
        
        if (isset($recipients[$current->getEmail()])) unset($recipients[$current->getEmail()]);

        return $recipients;
    }

    protected function _getRoute($entity)
    {
        $router = $this->getObject('com://site/easydoc.dispatcher.router.admin', ['request' => $this->getObject('request')]);

        return $router->generate($entity, ['page' => sprintf('easydoc-%s', strtolower(Library\StringInflector::pluralize($entity->getIdentifier()->getName())))]);
    }

    protected function _getUserRoute($user)
    {
        return WP::admin_url('/user-edit.php?user_id=' . $user->getId());
    }

    public function getData()
    {
        $data = parent::getData();

        $translator = $this->getObject('translator');

        $data['body'] = [
            'add'      => $translator->translate('Email notifier body add'),
            'edit'     => $translator->translate('Email notifier body edit'),
            'delete'   => $translator->translate('Email notifier body delete'),
            'download' => $translator->translate('Email notifier body download'),
            'generic'  => $translator->translate('Email notifier body generic')
        ];    

        return $data;
    }

    public function getUsers(Library\ModelEntityInterface $entity)
    {
        $result = [];

         if ($notifiers = $entity->getNotifications())
         {
            foreach ($notifiers as $notifier)
            {
                if ($users = $notifier->getParameters()->users) {
                    $result = array_merge($result, $users->toArray());
                }
            }
         }

         return $result;
    }
}