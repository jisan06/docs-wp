<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

abstract class NotifierAbstract extends Library\ObjectAbstract implements NotifierInterface
{
    protected $_actions;

    protected $_name;

    protected $_layout;

    protected $_package;

    protected $_job;

    const FIXED_GROUPS = ['category_owner' => -1, 'document_owner' => -2];

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_actions = Library\ObjectConfig::unbox($config->actions);
        $this->_layout  = $config->layout;
        $this->_name    = $config->name;
        $this->_package = $config->package;
        $this->_job     = $config->job;
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'package' => $config->object_identifier->getPackage(),
            'actions' => [],
            'name'    => strtolower($config->object_identifier->getName())
        ])->append([
            'layout' => sprintf('com:easydoc/notifier/%s.html', $config->name)
        ]);

        parent::_initialize($config);
    }

    abstract public function notify(NotifierContextInterface $context);

    public function getPackage()
    {
        return $this->_package;
    }

    public function getActions()
    {
        return $this->_actions;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function render($data = [])
    {
        $data['notifier'] = $this;

        return $this->getObject('com:easydoc.view.default.html')
                    ->setLayout($this->_layout)
                    ->render($data);
    }

    public function getData()
    {
        $result = ['actions' => []];

        $translator = $this->getObject('translator');

        foreach ($this->getActions() as $resource => $value)
        {
            $actions = (array) $value;

            foreach ($actions as $action)
            {
                $value = is_numeric($resource) ? $action : sprintf('%s_%s', $action, $resource);

                $key = $translator->generateKey(sprintf('notifier_action_%s', $value));

                $result['actions'][$value] = [
                    'label' => $translator->getCatalogue()->has($key) ? $translator->translate($key) : (is_numeric($resource) ? ucfirst($value) : sprintf('%s - %s', ucfirst($resource), ucfirst($action))),
                    'table' => sprintf('%s_%s', $this->getPackage(), Library\StringInflector::pluralize($resource))
                ];
            }
        }

        $result['identifier'] = (string) $this;
        $result['name']       = $translator->translate($this->getName());

        return $result;
    }

    public function getJob()
    {
        return $this->_job;
    }

    public function __toString()
    {
        return $this->getIdentifier()->toString();
    }
}
