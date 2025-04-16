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
 * Fragment View Behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\Component\Base
 */
class ViewBehaviorFragment extends Library\ViewBehaviorAbstract
{
    protected $_endpoint;

    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->_endpoint = $config->endpoint;
    }

    public function setEndpoint($endpoint)
    {
        $this->_endpoint = $endpoint;

        return $this;
    }

    public function getEndpoint()
    {
        return $this->_endpoint;
    }

    protected function _beforeRouting(Library\ViewContextInterface $context)
    {
        if (!isset($context->query['endpoint'])) {
            $context->query['endpoint'] = $this->getEndpoint();
        }
    }
}