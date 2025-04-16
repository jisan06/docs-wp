<?php
/**
 * @package     Foliokit Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Migrator;

use EasyDocLabs\Library;

class DispatcherBehaviorMigratable extends Library\ControllerBehaviorAbstract
{
    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(array(
            'priority'  => self::PRIORITY_HIGH,
            'exporters' => array(),
            'importers' => array(),
        ));

        parent::_initialize($config);
    }

    protected function _beforeDispatch(Library\DispatcherContextInterface $context)
    {
        $query = $context->request->query;

        if (in_array($query->view, array('export', 'import')))
        {
            $exporters = $this->getConfig()->exporters->toArray();
            $importers = $this->getConfig()->importers->toArray();

            if (!count($exporters)) {
                $package = $this->getMixer()->getIdentifier()->getPackage();
                $exporters[$package] = sprintf('com:%s.migrator.export', $package);
            }

            if (!count($importers)) {
                $package = $this->getMixer()->getIdentifier()->getPackage();
                $importers[$package] = sprintf('com:%s.migrator.import', $package);
            }

            $this->getIdentifier('com:migrator.controller.export')->getConfig()->append(array(
                'exporters' => $exporters
            ));

            $this->getIdentifier('com:migrator.controller.import')->getConfig()->append(array(
                'importers' => $importers
            ));


            $config = [
                'router'     => $context->router,
                'request'    => $context->request,
                'response'   => $context->response,
                'user'       => $context->user,
                'forwarded'  => $this
            ];
            $dispatcher = $this->getObject('com:migrator.dispatcher', $config);
            $dispatcher->dispatch($context);
            $dispatcher->send();

            return false;
        }
    }
}
