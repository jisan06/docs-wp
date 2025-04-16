<?php
/**
 * Foliokit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Activities;

use EasyDocLabs\Library;

/**
 * Activity Controller.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Activities
 */
class ControllerActivity extends Library\ControllerModel
{
    /**
     * Constructor.
     *
     * @param KObjectConfig $config Configuration options.
     */
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $translator = $this->getObject('translator');
        $catalogue = $translator->getCatalogue();

        if ($length = $catalogue->getConfig()->key_length) {
            $catalogue->getConfig()->key_length = false;
        }

        $translator->load('com:activities');

        if ($length) {
            $catalogue->getConfig()->key_length = $length;
        }
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'behaviors' => ['purgeable']
        ]);

        if ($this->getIdentifier()->getPackage() != 'activities')
        {
            $aliases = [
                'com:activities.model.activities'               => [
                    'path' => ['model'],
                    'name' => Library\StringInflector::pluralize($this->getIdentifier()->getName())
                ],
                'com:activities.controller.behavior.purgeable'  => [
                    'path' => ['controller', 'behavior'],
                    'name' => 'purgeable'
                ],
                'com:activities.controller.permission.activity' => ['path' => ['controller', 'permission']],
                'com:activities.controller.toolbar.activity'    => ['path' => ['controller', 'toolbar']]
            ];

            foreach ($aliases as $identifier => $alias)
            {
                $alias = array_merge($this->getIdentifier()->toArray(), $alias);

                $manager = $this->getObject('manager');

                // Register the alias if a class for it cannot be found.
                if (!$manager->getClass($alias, false)) {
                    $manager->registerAlias($identifier, $alias);
                }
            }
        }

        parent::_initialize($config);
    }

    /**
     * Method to set a view object attached to the controller
     *
     * @param   mixed   $view An object that implements Library\ObjectInterface, Library\ObjectIdentifier object
     *                  or valid identifier string
     * @return  object  A Library\ViewInterface object or a Library\ObjectIdentifier object
     */
    public function setView($view)
    {
        $view   = parent::setView($view);
        $format = $this->getRequest()->getFormat();

        if ($view instanceof Library\ObjectIdentifier && $view->getPackage() != 'activities' && $format  !== 'html')
        {
            $manager = $this->getObject('manager');

            // Set the view identifier as an alias of the component view.
            if (!$manager->getClass($view, false))
            {
                $identifier = $view->toArray();
                $identifier['package'] = 'activities';
                unset($identifier['domain']);

                $manager->registerAlias($identifier, $view);
            }
        }

        return $view;
    }

    /**
     * Overridden for forcing the package model state.
     */
    public function getRequest()
    {
        $request = parent::getRequest();

        // Force set the 'package' in the request
        $request->query->package = $this->getIdentifier()->package;

        return $request;
    }

    /**
     * Set the IP address if we are adding a new activity.
     *
     * @param Library\ControllerContextInterface $context A command context object.
     * @return Library\ModelEntityInterface
     */
    protected function _beforeAdd(Library\ControllerContextInterface $context)
    {
        $context->request->data->ip = $this->getObject('request')->getAddress();
    }
}
