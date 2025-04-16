<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Library;

/**
 * Html View
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Base
 */
class ViewHtml extends Library\ViewHtml
{
    /**
     * The view decorator
     *
     * @var string
     */
    private $__decorator;

    /**
     * Constructor
     *
     * @param   Library\ObjectConfig $config Configuration options
     */
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->setDecorator($config->decorator);
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append([
            'decorator'        => 'wordpress',
            'template_filters' => ['version', 'wrapper'],
            'template_functions' => [
                'decorator'   => [$this, 'getDecorator'],
            ],
        ]);

        parent::_initialize($config);
    }

    public function getDecorator()
    {
        return $this->__decorator;
    }

    public function setDecorator($decorator)
    {
        $this->__decorator = $decorator;

        return $this;
    }

    /**
     * Fetch the view data
     *
     * @param Library\ViewContextTemplate  $context A view context object
     * @return void
     */
    protected function _fetchData(Library\ViewContextTemplate $context)
    {
        parent::_fetchData($context);

        $context->parameters->decorator = $this->getDecorator();
    }
}