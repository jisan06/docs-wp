<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Component\Scheduler;
use EasyDocLabs\Library;

class JobLicense extends Scheduler\JobAbstract
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $secret = \EasyDocLabs\WP\AUTH_KEY;

        if (!$secret || strlen($secret) < 2) {
            $secret = str_replace('www.', '', $this->getObject('request')->getHost());
        }

        // We run the requests between 21:00UTC and 06:59UTC
        $hour = (ord($secret[0]) % 10) - 3;
        $minute = ord($secret[1]) % 60;

        if ($hour < 0) {
            $hour += 24;
        }

        $config->append([
            'frequency'   => "$minute $hour * * *"
        ]);


        parent::_initialize($config);
    }

    public function run(Scheduler\JobContextInterface $context)
    {
        try
        {
            $license = \Foliokit::getObject('license');
            
            $license->refresh();
        }
        catch (\Exception $e) {
            $context->log('exception: '.$e->getMessage());
        }

        return $this->complete();
    }
}