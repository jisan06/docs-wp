<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/nooku/nooku-framework-wordpress for the canonical source repository
 */

namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Library;

/**
 * Optionable Dispatcher Behavior
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class DispatcherBehaviorOptionable extends Library\ControllerBehaviorAbstract
{
    protected $_options;

	protected $_blocks;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

		$this->setOptionable($config);
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
		$config->append(['options' => [], 'blocks' => []]);

        parent::_initialize($config);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
	 * Options setter
	 *
     * @param array $options
     */
    public function setOptions($options)
    {
        if (!$options instanceof Library\ObjectConfig) {
            $options = new Library\ObjectConfig($options);
        }

        $this->_options = $options;

        return $this;
    }

	public function setOptionable($config)
    {
        if (!$config instanceof Library\ObjectConfig) {
            $config = new Library\ObjectConfig($config);
        }

		$this->_initialize($config);

		$this->setOptions($config->options);

		$this->_blocks = $config->blocks;

        return $this;
    }

    protected function _beforeDispatch(Library\ControllerContext $context)
    {
        $controller = $this->getController();

        if (!$controller->isOptionable()) {
            $controller->addBehavior('optionable');
        }

		$controller->setOptionable(['options' => $this->getOptions(), 'blocks' => $this->_blocks]);
    }
}
