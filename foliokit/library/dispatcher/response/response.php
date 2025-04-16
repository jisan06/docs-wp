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
 * Dispatcher Response Singleton
 *
 * Force the user object to a singleton with identifier alias 'response'.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Dispatcher\Response
 */
class DispatcherResponse extends DispatcherResponseAbstract implements ObjectSingleton
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
        $this->getObject('manager')->registerAlias($this->getIdentifier(), 'response');
    }
}