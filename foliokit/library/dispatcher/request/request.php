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
 * Dispatcher Request Singleton
 *
 * Force the user object to a singleton with identifier with alias 'request'.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Dispatcher\Request
 */
class DispatcherRequest extends DispatcherRequestAbstract implements ObjectSingleton
{
    /**
     * Constructor
     *
     * @param ObjectConfig  $config  A ObjectConfig object with optional configuration options
     */
    public function __construct(ObjectConfig $config)
    {
        parent::__construct($config);

        //Add a global object alias
        $this->getObject('manager')->registerAlias($this->getIdentifier(), 'request');
    }
}