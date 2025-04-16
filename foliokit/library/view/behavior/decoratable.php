<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace  EasyDocLabs\Library;

/**
 * Decoratable View Behavior
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package EasyDocLabs\Library\View\Behavior
 */
class ViewBehaviorDecoratable extends ViewBehaviorAbstract
{
    /**
     * The decorators
     *
     * @var array
     */
    private $__decorators = array();

    /**
     * Constructor
     *
     * @param ObjectConfig $config   An optional ObjectConfig object with configuration options
     */
    public function __construct(ObjectConfig $config)
    {
        parent::__construct($config);

        //Register the decorators
        foreach($config->decorators as $decorator) {
            $this->addDecorator($decorator);
        }
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param  ObjectConfig $config A ObjectConfig object with configuration options
     * @return void
     */
    protected function _initialize(ObjectConfig $config)
    {
        $config->append(array(
            'decorators' => array(),
        ));

        parent::_initialize($config);
    }

    /**
     * Add a decorator
     *
     * @param  string $identifier The decorator identifier
     * @param  bool $prepend      If true, the decorator will be prepended instead of appended.
     * @return ViewBehaviorDecoratable
     */
    public function addDecorator($identifier, $prepend = false)
    {
        if($prepend) {
            array_unshift($this->__decorators, $identifier);
        } else {
            array_push($this->__decorators, $identifier);
        }

        return $this;
    }

    /**
     * Get the list of decorators
     *
     * @return array The decorators
     */
    public function getDecorators()
    {
        return $this->__decorators;
    }

    /**
     * Decorate the view
     *
     * @param ViewContextInterface $context	A view context object
     * @return void
     */
    protected function _afterRender(ViewContextInterface $context)
    {
        foreach ($this->getDecorators() as $decorator)
        {
            //Set the content to allow it to be decorated
            $this->setContent($context->result);

            //A fully qualified template path is required
            $layout = $this->qualifyLayout($decorator);

            //Unpack the data (first level only)
            $data = $context->data->toArray();

            $context->result = $this->getTemplate()
                ->setParameters($context->parameters)
                ->render($layout, $data);
        }
    }
}