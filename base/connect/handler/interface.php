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
 * Connect handler interface
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package EasyDocLabs\EasyDoc
 */
interface ConnectHandlerInterface
{
    /**
     * Handler priority levels
     */
    const PRIORITY_HIGHEST = 1;
    const PRIORITY_HIGH    = 2;
    const PRIORITY_NORMAL  = 3;
    const PRIORITY_LOW     = 4;
    const PRIORITY_LOWEST  = 5;

    /**
     * Checks if an incoming request should be handled
     *
     * @param Library\ControllerContextInterface $context
     * @return mixed
     */
    public function canHandle(Library\ControllerContextInterface $context);

    /**
     * Handles an incoming request
     *
     * @param Library\ControllerContextInterface $context
     * @return mixed
     */
    public function handle(Library\ControllerContextInterface $context);

    /**
     * Get the priority of the handler
     *
     * @return integer The handler priority
     */
    public function getPriority();

    /**
     * Whether the handler requires an authorization token
     *
     * @return bool
     */
    public function requiresAuthorization();
}