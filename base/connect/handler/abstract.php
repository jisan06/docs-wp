<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

/**
 * Abstract connect handler
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\EasyDoc
 */
abstract class ConnectHandlerAbstract extends Library\ObjectAbstract implements ConnectHandlerInterface
{
    /**
     * Supported tasks
     *
     * @var array
     */
    protected $_tasks;

    /**
     * Property for task lookup on request
     *
     * @var string
     */
    protected $_property;

    /**
     * The handler priority
     *
     * @var integer
     */
    protected $_priority;

    /**
     * Connect endpoint instance
     *
     * @var ConnectEndpoint
     */
    protected $_endpoint;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_tasks    = Library\ObjectConfig::unbox($config->tasks);
        $this->_property = $config->property;
        $this->_priority = $config->priority;

        if ($config->connect instanceof ConnectEndpoint) {
            $this->_endpoint = $config->connect;
        } else {
            throw new \RuntimeException('Connect endpoint instance is missing');
        }
    }

    protected function _initialize(Library\ObjectConfig $config)
    {
        $config->append(['tasks' => [], 'property' => 'task', 'priority' => self::PRIORITY_NORMAL]);

        parent::_initialize($config);
    }

    /**
     * Checks if an incoming request should be handled
     *
     * @param Library\ControllerContextInterface $context
     * @return mixed
     */
    public function canHandle(Library\ControllerContextInterface $context)
    {
        $result = false;

        $query = $context->getRequest()->getQuery();

        if (isset($query[$this->_property]) && in_array($query[$this->_property], $this->_tasks)) {
            $result = true;
        }

        return $result;
    }

    /**
     * Handles an incoming request
     *
     * @param Library\ControllerContextInterface $context
     * @return mixed
     */
    public function handle(Library\ControllerContextInterface $context)
    {
        $result = false;

        $query = $context->getRequest()->getQuery();

        $task = $query[$this->_property];

        $method = sprintf('_task%s', Library\StringInflector::camelize(str_replace(['-','_'], '', $task)));

        if (method_exists($this, $method)) {
            $result = call_user_func([$this, $method], $context);
        }

        return $result;
    }

    /**
     * Get the priority of the handler
     *
     * @return integer The handler priority
     */
    public function getPriority()
    {
        return $this->_priority;
    }

    public function requiresAuthorization()
    {
        return true;
    }
}