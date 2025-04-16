<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Component\Scheduler;
use EasyDocLabs\Library;

class JobEmails extends Scheduler\JobAbstract
{
    protected $_send_limit;

    protected $_purge_limit;

    protected $_retries_limit;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_send_limit    = $config->send_limit;
        $this->_purge_limit   = $config->purge_limit;
        $this->_retries_limit = $config->retries_limit;
    }

    protected function _initialize(Library\ObjectConfig $config)
    {        
        $config->append(array(
            'frequency'  => Scheduler\JobInterface::FREQUENCY_EVERY_MINUTE,
            'send_limit' => 25, // Send a max of 25 emails per run
            'purge_limit' => 3, // Cleanup sent emails every 3 days
            'retries_limit' => 3 // Abandon failed emails after 3 attemps
        ));

        parent::_initialize($config);
    }

    public function run(Scheduler\JobContextInterface $context)
    {
        try
        {           
            $this->_handleFailed($context);

            $this->_purgeSent($context);

            $emails = $this->getObject('com:easydoc.model.emails')
                        ->status(NotifierEmail::STATUS_PENDING)
                        ->sort('created_on')
                        ->limit($this->_send_limit)
                        ->fetch();

            foreach ($emails as $email)
            {
                if (NotifierEmail::send($email->recipient, $email->subject, $email->body))
                {
                    $message = sprintf('E-mail with ID %s has been sent', $email->id);

                    $email->status  = NotifierEmail::STATUS_SENT;
                    $email->sent_on = gmdate('Y-m-d H:i:s', time());
                }
                else
                {
                    $message = sprintf('E-mail with ID %s could not be sent, current retry count is %s', $email->id, $email->retries);

                    $email->status  = NotifierEmail::STATUS_FAILED;
                    $email->retries = $email->retries + 1;
                }

                $context->log($message);

                if (($email->status == NotifierEmail::STATUS_FAILED) && ($email->retries > $this->_retries_limit))
                {
                    // Abandon the email

                    $message = sprintf('E-mail with ID %s from notification with ID %s with recipient %s has been abandoned after %s attempts.', $email->id, $email->notification, $email->recipient, $email->retries);

                    $context->log($message);

                    $email->delete();
                }
                else $email->save();

                if (!$context->hasTimeLeft()) break;
            }
        }
        catch (\Exception $e)
        {
            $context->log($e->getMessage());
        }

        return $this->complete();
    }

    protected function _purgeSent(Scheduler\JobContextInterface $context)
    {
        $driver = $this->getObject('lib:database.driver.mysqli');

        $query = $this->getObject('lib:database.query.delete');

        $query->table('easydoc_emails')
            ->where('status = :status')
            ->where('UTC_TIMESTAMP() > DATE_ADD(sent_on, INTERVAL :purge_limit DAY)')
            ->bind(['status' => NotifierEmail::STATUS_SENT, 'purge_limit' => $this->_purge_limit]);

        $deleted = $driver->delete($query);

        if ($deleted > 0) {
            $context->log(sprintf('%s EasyDocs sent emails have been purged', $deleted));
        }
    }

    protected function _handleFailed(Scheduler\JobContextInterface $context)
    {
        $query = $this->getObject('lib:database.query.update');

        $query->table('easydoc_emails')
              ->values('status = :pending')
              ->bind(array('pending' => NotifierEmail::STATUS_PENDING));

        for ($i = 1; $i <= $this->_retries_limit; $i++)
        {
            $condition = sprintf('(retries = :retries_%1$s AND status = :failed AND DATE_ADD(created_on, INTERVAL %2$s HOUR) < UTC_TIMESTAMP())', $i, pow(2, $i) - 1);

            $query->where($condition, 'OR')->bind(array(
                sprintf('retries_%1$s', $i) => $i
            ));
        }

        $query->bind(array('failed' => NotifierEmail::STATUS_FAILED));

        $adapter = $this->getObject('lib:database.driver.mysqli');

        if ($result = $adapter->update($query)) {
            $context->log(sprintf('%s failed EasyDocs emails have been reset to pending', $result));
        }

        $query = $this->getObject('lib:database.query.update');
    }
}