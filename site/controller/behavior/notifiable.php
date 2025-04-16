<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;
use EasyDocLabs\WP;

class ControllerBehaviorNotifiable extends Library\ControllerBehaviorAbstract
{
    protected function _afterAdd(Library\ControllerContextInterface $context)
    {
        if ($context->getResponse()->getStatusCode() == Library\HttpResponse::CREATED)
        {
            $options = $this->getOptions();

            $translator = $this->getObject('translator');

            $emails = $options->get('notification_emails');
            if (!empty($emails))
            {
                $emails = Library\ObjectConfig::unbox($emails);

                if (is_string($emails)) {
                    $emails = explode("\n", $emails);
                }
                
                $from_name = WP::get_option('blogname');
                $mail_from = WP::get_option('admin_email');
                $sitename  = $from_name;
                $subject   = $translator->translate('A new document was submitted for you to review on {sitename}', [
                    'sitename' => $sitename
                ]);

                $headers     = [
                    'Content-Type: text/html; charset=UTF-8',
                    "From: {$from_name} <{$mail_from}>"
                ];
                $admin_link  = $context->request->getSiteUrl().'/wp-admin/admin.php?component=easydoc&view=documents&page=easydoc-documents';
                $title       = $context->result->title;
                $admin_title = $translator->translate('Document Manager');

                $template = $this->getObject('com:koowa.view.html')->getTemplate();

                foreach ($emails as $email)
                {
                    $body = $template->render('com://site/easydoc/email/upload.html', [
                        'document' => $context->result,
                        'email'    => $email,
                        'title'    => $title,
                        'sitename' => $sitename,
                        'url'      => $admin_link,
                        'url_text' => $admin_title
                    ]);

                    WP::wp_mail($email, $subject, $body, $headers);
                }
            }
        }
    }
}
