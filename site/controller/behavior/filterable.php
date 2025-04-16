<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc\Site;

use EasyDocLabs\Library;

/**
 * Filterable Controller Behavior
 *
 * Modifies the request based on its current state, active page parameters and format.
 */
class ControllerBehaviorFilterable extends Library\ControllerBehaviorAbstract
{
    protected $_options;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_options = $config->options;
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'priority' => Library\CommandHandlerAbstract::PRIORITY_LOW
        ]);

        if (empty($config->options)) {
            $config->options = ['sort'];
        }

        parent::_initialize($config);
    }

    protected function _beforeRender(Library\ControllerContextInterface $context)
    {
        $request = $context->getRequest();

        if ($request->isGet())
        {
            $controller = $context->getSubject();

            foreach ($this->_options as $name => $param)
            {
                if (is_numeric($name)) $name = $param;

                $method = '_set' . ucfirst($name);

                if (!method_exists($this, $method))
                {
                    if ($value = $this->getOptions()->get($param)) {
                        $request->query->set($name, Library\ObjectConfig::unbox($value)); // Only modify the request if we have a value
                    }
                }
                else call_user_func_array([$this, $method], [$request, $param]);
            }

            if ($request->getFormat() == 'rss')
            {
                $query  = $request->getQuery();
                $states = ['limit' => 20, 'offset' => 0, 'sort' => 'created_on', 'direction' => 'desc'];

                foreach ($states as $name => $value)
                {
                    $query->set($name, $value);

                    // Set as internal.
                    $controller->getModel()->getState()->setProperty($name, 'internal', true);
                }
            }

            // Update the model state.
            $controller->getModel()->setState($request->getQuery()->toArray());
        }
    }

    protected function _setSort_categories(Library\ControllerRequestInterface $request, $param)
    {
        $value = $this->getOptions()->get($param);

        if (substr($value, 0, 8) === 'reverse_')
        {
            $sort      = substr($value, 8);
            $direction = 'desc';
        }
        else
        {
            $sort      = $value;
            $direction = 'asc';
        }

        $this->_setOption($param, $sort);
        $this->_setOption('direction_categories', $direction);
    }

    /**
     * Sort setter.
     *
     * @param Library\ControllerRequestInterface $request  The controller request object.
     * @param string                      $param    The page parameter name containing the selected value.
     */
    protected function _setSort(Library\ControllerRequestInterface $request, $param)
    {
        $query = $request->getQuery();
        $value = $this->getOptions()->get($param);

        if (substr($value, 0, 8) === 'reverse_')
        {
            $sort      = substr($value, 8);
            $direction = 'desc';
        }
        else
        {
            $sort      = $value;
            $direction = 'asc';
        }

        // Page settings are considered as default.
        $this->getModel()->getState()->setProperty('sort', 'default', $sort)
             ->setProperty('direction', 'default', $direction);

        // Set from page settings if not set.
        $query->sort      = $query->sort ? $query->sort : $sort;
        $query->direction = $query->direction ? $query->direction : $direction;

        // Disallow arbitrary sorting.
        if (!in_array($query->sort, ['hits', 'title', 'created_on', 'touched_on'])) {
            $query->sort = $sort;
        }

        if (!$this->getOptions()->get('show_document_sort_limit'))
        {
            $query->sort      = $sort;
            $query->direction = $direction;

            // Set as internal.
            $this->getModel()->getState()->setProperty('sort', 'internal', true)
                 ->setProperty('direction', 'internal', true);
        }
    }

    protected function _setOption($name, $value)
    {
        $mixer = $this->getMixer();

        if ($mixer->isOptionable())
        {
            $mixer->getOptions()->set($name, $value);
        
            if ($mixer instanceof Library\ControllerViewable)
            {
                $view = $mixer->getView();
            
                if ($view->isOptionable()) {
                    $view->getOptions()->set($name, $value);
                }
            }
        }

        return $this;
    }
}