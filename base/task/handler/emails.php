<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class TaskHandlerEmails
{
    public function run($context)
    {
        $task = $this->getObject('com:easydoc.model.tasks')->uuid($context->uuid)->fetch();

        if (!$this->sendEmail($task->getMetadata())) {
            // TODO Log stuff and set context status
        }
    }

    public function sendEmail($data)
    {
        if (!empty($data->recipient))
        {
            $from_name = WP::get_option('blogname');
            $mail_from = WP::get_option('admin_email');
            $sitename  = $from_name;

            $translator = $this->getObject('translator');

            $subject = $translator->translate('A new document was submitted for you to review on {sitename}', [
                'sitename' => $sitename
            ]);

            $headers = [
                'Content-Type: text/html; charset=UTF-8',
                "From: {$from_name} <{$mail_from}>"
            ];

            $admin_link  = $this->getObject('request')->getSiteUrl() .
                           '/wp_admin/admin.php?component=easydoc&view=documents&page=easydoc-documents';

            $layout = sprintf('com:easydoc/notifier/email.%s.%s.html', $notification->table, $notification->action);


                $body = $this->getObject('com:easydoc.view.default.html')
                             ->setLayout($layout)
                             ->render([
                                 'subject'  => $notification->getSubject(),
                                 'email'    => $recipient,
                                 'sitename' => $sitename,
                                 'url'      => $admin_link,
                                 'url_text' => 'Document Manager'
                             ]);

                WP::wp_mail($recipient, $data->subject, $data->body, $headers);
            }
        }
    }
}