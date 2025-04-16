<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */

namespace EasyDocLabs\Library;

/**
 * Dispatcher Context
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Dispatcher\Context
 */
class DispatcherContext extends ControllerContext implements DispatcherContextInterface
{
    /**
     * The request has been successfully authenticated
     *
     * @return Boolean
     */
    public function isAuthentic()
    {
        return (bool) ObjectConfig::get('authentic', $this->getUser()->isAuthentic(true));
    }

    /**
     * Sets the request as authenticated
     *
     * @return $this
     */
    public function setAuthentic()
    {
        ObjectConfig::set('authentic', true);
        return $this;
    }
}