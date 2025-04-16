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

class JobMultidownload extends Scheduler\JobAbstract
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'frequency'   => Scheduler\JobInterface::FREQUENCY_HOURLY
        ]);

        parent::_initialize($config);
    }

    public function run(Scheduler\JobContextInterface $context)
    {
        /**
         * @var $behavior \EasyDocLabs\EasyDoc\Site\ControllerBehaviorCompressible
         */
        $behavior = $this->getObject('com://site/easydoc.controller.behavior.compressible');

        if (!$behavior->isSupported()) {
            return $this->skip();
        }

        $behavior->purgeExpiredFiles();

        return $this->complete();
    }
}