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
 * Command Handler Interface
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package EasyDocLabs\Library\Command\Handler
 */
interface CommandHandlerInterface extends ObjectHandlable
{
    /**
     * Priority levels
     */
    const PRIORITY_HIGHEST = 1;
    const PRIORITY_HIGH    = 2;
    const PRIORITY_NORMAL  = 3;
    const PRIORITY_LOW     = 4;
    const PRIORITY_LOWEST  = 5;

    /**
     * Execute the handler
     *
     * @param CommandInterface         $command    The command
     * @param CommandChainInterface    $chain      The chain executing the command
     * @return mixed|null If the handler breaks, returns the break condition. NULL otherwise.
     */
    public function execute(CommandInterface $command, CommandChainInterface $chain);

    /**
     * Get the priority of the handler
     *
     * @return	integer The invoker priority
     */
    public function getPriority();
}
