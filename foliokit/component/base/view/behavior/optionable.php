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
 * Optionable View Behavior
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package EasyDocLabs\Component\Base
 */
class ViewBehaviorOptionable extends Library\ViewBehaviorAbstract
{
    /**
     * The options
     *
     * @var Library\ObjectConfigInterface
     */
    protected $_options;

	/**
	 * JWT encoded query options forwarded by the controller optionable behavior
	 *
	 * These are options listed as referrable by the loaded blocks with their corresponding values
	 *
	 * @var string
	 */
	protected $_query_options;

	public function __construct(Library\ObjectConfig $config)
	{
		parent::__construct($config);

		$this->setOptions($config->options);

		$this->_query_options = $config->query_options;
	}

    protected function _beforeRender(Library\ViewContextInterface $context)
    {
        if ($this->getMixer() instanceof Library\ViewTemplatable) {
            $template = $this->getMixer()->getTemplate();

            $template->registerFunction('option', [$this, 'option']);
            $template->registerFunction('options', [$this, 'options']);
        }
    }

    public function setOption($key, $value) {
        return $this->getOptions()->set($key, $value);
    }

    public function getOption($key, $default = null)
    {
        return $this->getOptions()->get($key, $default);
    }

    public function setOptions($options)
    {
		$this->_options = new Library\ObjectConfig();

		foreach ($options as $key => $value) {
			$this->setOption($key, $value);
		}
    }

	public function getOptions()
    {
        return $this->_options;
    }

    public function option($key, $default = null) {
        return $this->getOption($key, $default);
    }

    public function options() {
        return $this->getOptions();
    }

	public function getQueryOptions()
	{
		return $this->_query_options;
	}
}
